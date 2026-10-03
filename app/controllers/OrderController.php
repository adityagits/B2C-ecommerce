<?php

class OrderController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();
        $this->view('orders/index', [
            'title'  => 'My Orders',
            'orders' => (new Order())->forUser((int) $user['id']),
        ]);
    }

    public function show(string $id): void
    {
        [$orders, $order] = $this->owned($id);
        $this->view('orders/show', [
            'title'    => "Order #{$order['id']}",
            'order'    => $order,
            'items'    => $orders->items((int) $id),
            'invoice'  => $orders->invoice((int) $id),
            'payment'  => $orders->payment((int) $id),
            'shipment' => $orders->shipment((int) $id),
            'events'   => $orders->events((int) $id),
        ]);
    }

    public function invoice(string $id): void
    {
        [$orders, $order] = $this->owned($id);
        $invoice = $orders->invoice((int) $id) ?? $this->notFound();
        $this->view('orders/invoice', [
            'title'   => $invoice['invoice_number'],
            'order'   => $order,
            'invoice' => $invoice,
            'items'   => $orders->items((int) $id),
            'payment' => $orders->payment((int) $id),
        ], 'layout/print');
    }

    public function cancel(string $id): void
    {
        [$orders, $order] = $this->owned($id, false);
        if (!in_array($order['status'], ['pending', 'paid'], true)) {
            flash('error', 'This order can no longer be cancelled.');
        } else {
            $orders->cancel((int) $id);
            flash('success', 'Order cancelled' . ($order['status'] === 'paid' ? ' and your payment refunded.' : '.'));
        }
        $this->redirect("/orders/$id");
    }

    /** Load an order the current user may see (owner, or admin when $allowAdmin). */
    private function owned(string $id, bool $allowAdmin = true): array
    {
        $user = $this->requireLogin();
        $orders = new Order();
        $order = $orders->find((int) $id);
        if (!$order || ((int) $order['user_id'] !== (int) $user['id'] && !($allowAdmin && is_admin()))) {
            $this->notFound();
        }
        return [$orders, $order];
    }
}
