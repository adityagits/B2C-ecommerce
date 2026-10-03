<?php

class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

    /**
     * Create an order from cart lines atomically; decrements stock.
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
            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        return $this->one('SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?', [$id]);
    }

    public function items(int $orderId): array
    {
        return $this->all('SELECT * FROM order_items WHERE order_id = ?', [$orderId]);
    }

    public function forUser(int $userId): array
    {
        return $this->all('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC', [$userId]);
    }

    public function adminList(?string $status): array
    {
        $sql = 'SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id';
        $params = [];
        if ($status && in_array($status, self::STATUSES, true)) {
            $sql .= ' WHERE o.status = ?';
            $params[] = $status;
        }
        return $this->all($sql . ' ORDER BY o.created_at DESC, o.id DESC', $params);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->run('UPDATE orders SET status = ? WHERE id = ?', [$status, $id]);
    }

    /** Cancel an order and return its stock. */
    public function cancel(int $id): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($this->items($id) as $i) {
                $this->run('UPDATE products SET stock = stock + ? WHERE id = ?', [$i['quantity'], $i['product_id']]);
            }
            $this->setStatus($id, 'cancelled');
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function stats(): array
    {
        return [
            'orders'  => (int) $this->value('SELECT COUNT(*) FROM orders'),
            'pending' => (int) $this->value("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
            'revenue' => (float) $this->value("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'"),
        ];
    }

    public function recent(int $limit = 5): array
    {
        return $this->all("SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC, o.id DESC LIMIT $limit");
    }
}
