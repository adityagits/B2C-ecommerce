<?php

class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    public const SHIPMENT_STATUSES = ['processing', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'returned'];
    public const IN_TRANSIT = ['shipped', 'in_transit', 'out_for_delivery'];

    // ---------------------------------------------------------------- placing

    /**
     * Create an order, its invoice, payment and shipment atomically; decrements stock.
     * @throws RuntimeException when stock is insufficient.
     */
    public function place(int $userId, array $lines, array $totals, array $ship, string $payment): int
    {
        $this->db->beginTransaction();
        try {
            $this->run(
                'INSERT INTO orders (user_id, subtotal, shipping, tax, total, payment_method, shipping_name, shipping_address, shipping_city, shipping_zip, shipping_phone)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [$userId, $totals['subtotal'], $totals['shipping'], $totals['tax'], $totals['total'], $payment,
                 $ship['name'], $ship['address'], $ship['city'], $ship['zip'], $ship['phone']]
            );
            $orderId = (int) $this->db->lastInsertId();

            foreach ($lines as $l) {
                $p = $l['product'];
                $updated = $this->run(
                    'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?',
                    [$l['qty'], $p['id'], $l['qty']]
                );
                if ($updated === 0) {
                    throw new RuntimeException("Sorry, \"{$p['name']}\" no longer has enough stock.");
                }
                $this->run(
                    'INSERT INTO order_items (order_id, product_id, name, price, quantity) VALUES (?,?,?,?,?)',
                    [$orderId, $p['id'], $p['name'], $p['price'], $l['qty']]
                );
            }

            // Invoice (number is sequential because order ids are)
            $paidNow = $payment === 'card'; // demo card is "charged" immediately
            $this->run(
                'INSERT INTO invoices (order_id, invoice_number, billing_name, billing_address, subtotal, shipping, tax, total, status)
                 VALUES (?,?,?,?,?,?,?,?,?)',
                [$orderId, sprintf('INV-%s-%05d', date('Y'), $orderId), $ship['name'],
                 "{$ship['address']}, {$ship['city']} {$ship['zip']}",
                 $totals['subtotal'], $totals['shipping'], $totals['tax'], $totals['total'], $paidNow ? 'paid' : 'unpaid']
            );

            // Payment transaction
            $txn = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
            $this->run(
                'INSERT INTO payments (order_id, transaction_id, method, amount, status, paid_at) VALUES (?,?,?,?,?,?)',
                [$orderId, $txn, $payment, $totals['total'], $paidNow ? 'paid' : 'pending', $paidNow ? date('Y-m-d H:i:s') : null]
            );
            if ($paidNow) {
                $this->setStatus($orderId, 'paid');
            }

            $this->run('INSERT INTO shipments (order_id) VALUES (?)', [$orderId]);

            $this->event($orderId, 'order', 'Order placed', $userId);
            $this->event($orderId, 'payment', $paidNow ? "Payment received ($txn)" : 'Awaiting payment (cash on delivery)', $userId);

            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ---------------------------------------------------------------- reading

    public function find(int $id): ?array
    {
        return $this->one('SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?', [$id]);
    }

    public function items(int $orderId): array
    {
        return $this->all('SELECT * FROM order_items WHERE order_id = ?', [$orderId]);
    }

    public function invoice(int $orderId): ?array
    {
        return $this->one('SELECT * FROM invoices WHERE order_id = ?', [$orderId]);
    }

    public function payment(int $orderId): ?array
    {
        return $this->one('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1', [$orderId]);
    }

    public function shipment(int $orderId): ?array
    {
        return $this->one('SELECT * FROM shipments WHERE order_id = ?', [$orderId]);
    }

    public function events(int $orderId): array
    {
        return $this->all(
            'SELECT e.*, u.name AS user_name FROM order_events e LEFT JOIN users u ON u.id = e.created_by WHERE e.order_id = ? ORDER BY e.id DESC',
            [$orderId]
        );
    }

    public function forUser(int $userId): array
    {
        return $this->all(
            'SELECT o.*, s.status AS shipment_status FROM orders o LEFT JOIN shipments s ON s.order_id = o.id
             WHERE o.user_id = ? ORDER BY o.created_at DESC, o.id DESC',
            [$userId]
        );
    }

    public function adminList(?string $status): array
    {
        $sql = 'SELECT o.*, u.name AS customer_name, i.invoice_number, p.status AS payment_status, s.status AS shipment_status
                FROM orders o JOIN users u ON u.id = o.user_id
                LEFT JOIN invoices i ON i.order_id = o.id
                LEFT JOIN payments p ON p.order_id = o.id
                LEFT JOIN shipments s ON s.order_id = o.id';
        $params = [];
        if ($status && in_array($status, self::STATUSES, true)) {
            $sql .= ' WHERE o.status = ?';
            $params[] = $status;
        }
        return $this->all($sql . ' ORDER BY o.created_at DESC, o.id DESC', $params);
    }

    public function allInvoices(): array
    {
        return $this->all('SELECT i.*, u.name AS customer_name FROM invoices i JOIN orders o ON o.id = i.order_id JOIN users u ON u.id = o.user_id ORDER BY i.id DESC');
    }

    public function allPayments(): array
    {
        return $this->all('SELECT p.*, u.name AS customer_name FROM payments p JOIN orders o ON o.id = p.order_id JOIN users u ON u.id = o.user_id ORDER BY p.id DESC');
    }

    public function stats(): array
    {
        return [
            'orders'  => (int) $this->value('SELECT COUNT(*) FROM orders'),
            'pending' => (int) $this->value("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
            'revenue' => (float) $this->value("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'paid'"),
            'to_ship' => (int) $this->value("SELECT COUNT(*) FROM orders WHERE status IN ('pending','paid')"),
        ];
    }

    public function recent(int $limit = 5): array
    {
        return $this->all("SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC, o.id DESC LIMIT $limit");
    }

    // ---------------------------------------------------------------- changing

    public function setStatus(int $id, string $status): void
    {
        $this->run('UPDATE orders SET status = ? WHERE id = ?', [$status, $id]);
    }

    /**
     * Record a payment outcome: paid | failed | refunded.
     * @throws RuntimeException on an invalid transition.
     */
    public function setPayment(int $orderId, string $status): void
    {
        $order = $this->find($orderId);
        $pay = $this->payment($orderId);
        if (!$order || !$pay) {
            throw new RuntimeException('Order not found.');
        }
        if ($order['status'] === 'cancelled') {
            throw new RuntimeException('This order is cancelled.');
        }

        $this->db->beginTransaction();
        try {
            switch ($status) {
                case 'paid':
                    if ($pay['status'] === 'paid') throw new RuntimeException('Payment is already marked paid.');
                    if ($pay['status'] === 'refunded') throw new RuntimeException('Refunded payments cannot be re-opened.');
                    $this->markPaid($order, $pay);
                    $this->event($orderId, 'payment', "Payment received ({$pay['transaction_id']})");
                    break;
                case 'failed':
                    if ($pay['status'] !== 'pending') throw new RuntimeException('Only pending payments can be marked failed.');
                    $this->run("UPDATE payments SET status = 'failed' WHERE id = ?", [$pay['id']]);
                    $this->event($orderId, 'payment', "Payment failed ({$pay['transaction_id']})");
                    break;
                case 'refunded':
                    if ($pay['status'] !== 'paid') throw new RuntimeException('Only paid payments can be refunded.');
                    $this->run("UPDATE payments SET status = 'refunded', refunded_at = NOW() WHERE id = ?", [$pay['id']]);
                    $this->run("UPDATE invoices SET status = 'void' WHERE order_id = ?", [$orderId]);
                    $this->event($orderId, 'payment', "Payment refunded ({$pay['transaction_id']})");
                    break;
                default:
                    throw new RuntimeException('Invalid payment action.');
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function markPaid(array $order, array $pay): void
    {
        $this->run("UPDATE payments SET status = 'paid', paid_at = NOW(), refunded_at = NULL WHERE id = ?", [$pay['id']]);
        $this->run("UPDATE invoices SET status = 'paid' WHERE order_id = ?", [$order['id']]);
        if ($order['status'] === 'pending') {
            $this->setStatus((int) $order['id'], 'paid');
        }
    }

    /**
     * Update delivery details/status. Keeps order status, payment (COD is collected on
     * delivery) and the timeline in sync.
     * @param array{carrier:?string,tracking:?string,status:string,eta:?string} $d
     * @throws RuntimeException on invalid input or transition.
     */
    public function updateShipment(int $orderId, array $d): void
    {
        $order = $this->find($orderId);
        $ship = $this->shipment($orderId);
        if (!$order || !$ship) {
            throw new RuntimeException('Order not found.');
        }
        if ($order['status'] === 'cancelled') {
            throw new RuntimeException('This order is cancelled.');
        }
        if (!in_array($d['status'], self::SHIPMENT_STATUSES, true)) {
            throw new RuntimeException('Invalid shipment status.');
        }
        if ($ship['status'] === 'delivered' && $d['status'] !== 'returned' && $d['status'] !== 'delivered') {
            throw new RuntimeException('A delivered shipment can only be marked returned.');
        }
        if ($d['eta'] !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d['eta'])) {
            throw new RuntimeException('Estimated delivery must be a valid date.');
        }
        if (in_array($d['status'], ['shipped', 'in_transit', 'out_for_delivery', 'delivered'], true) && ($d['carrier'] ?? '') === '' && ($ship['carrier'] ?? '') === '') {
            throw new RuntimeException('Enter a carrier before marking the order as shipped.');
        }

        $this->db->beginTransaction();
        try {
            $newStatus = $d['status'];
            $carrier = $d['carrier'] ?: $ship['carrier'];
            $this->run(
                'UPDATE shipments SET carrier = ?, tracking_number = ?, estimated_delivery = ?, status = ? WHERE order_id = ?',
                [$carrier, $d['tracking'] ?: null, $d['eta'] ?: null, $newStatus, $orderId]
            );

            if (in_array($newStatus, self::IN_TRANSIT, true)) {
                $this->run('UPDATE shipments SET shipped_at = COALESCE(shipped_at, NOW()), delivered_at = NULL WHERE order_id = ?', [$orderId]);
                $this->setStatus($orderId, 'shipped');
            } elseif ($newStatus === 'delivered') {
                $this->run('UPDATE shipments SET shipped_at = COALESCE(shipped_at, NOW()), delivered_at = COALESCE(delivered_at, NOW()) WHERE order_id = ?', [$orderId]);
                $this->setStatus($orderId, 'delivered');
                $pay = $this->payment($orderId);
                if ($pay && $pay['status'] === 'pending') {
                    $this->markPaid($order, $pay);
                    $this->event($orderId, 'payment', "Payment collected on delivery ({$pay['transaction_id']})");
                }
            } elseif ($newStatus === 'processing') {
                $this->run('UPDATE shipments SET shipped_at = NULL, delivered_at = NULL WHERE order_id = ?', [$orderId]);
                $pay = $this->payment($orderId);
                $this->setStatus($orderId, $pay && $pay['status'] === 'paid' ? 'paid' : 'pending');
            }

            if ($newStatus !== $ship['status'] || $carrier !== $ship['carrier'] || ($d['tracking'] ?: null) !== $ship['tracking_number']) {
                $msg = 'Shipment: ' . ucfirst(str_replace('_', ' ', $newStatus));
                if ($carrier) $msg .= " via $carrier";
                if ($d['tracking']) $msg .= " (tracking {$d['tracking']})";
                $this->event($orderId, 'shipment', $msg);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Cancel an order: restock items, refund/void payment and invoice, cancel the shipment. */
    public function cancel(int $id): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($this->items($id) as $i) {
                $this->run('UPDATE products SET stock = stock + ? WHERE id = ?', [$i['quantity'], $i['product_id']]);
            }
            $this->setStatus($id, 'cancelled');

            $pay = $this->payment($id);
            if ($pay && $pay['status'] === 'paid') {
                $this->run("UPDATE payments SET status = 'refunded', refunded_at = NOW() WHERE id = ?", [$pay['id']]);
                $this->event($id, 'payment', "Payment refunded ({$pay['transaction_id']})");
            } elseif ($pay && $pay['status'] === 'pending') {
                $this->run("UPDATE payments SET status = 'failed' WHERE id = ?", [$pay['id']]);
            }
            $this->run("UPDATE invoices SET status = 'void' WHERE order_id = ?", [$id]);
            $this->run("UPDATE shipments SET status = 'cancelled' WHERE order_id = ?", [$id]);
            $this->event($id, 'order', 'Order cancelled');
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function event(int $orderId, string $type, string $message, ?int $by = null): void
    {
        $this->run(
            'INSERT INTO order_events (order_id, type, message, created_by) VALUES (?,?,?,?)',
            [$orderId, $type, $message, $by ?? (auth_user()['id'] ?? null)]
        );
    }
}
