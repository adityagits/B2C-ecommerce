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
        $user = $this->requireLogin();
        $orders = new Order();
        $order = $orders->find((int) $id);
        if (!$order || ((int) $order['user_id'] !== (int) $user['id'] && !is_admin())) {
            $this->notFound();
        }
        $this->view('orders/show', [
            'title' => "Order #{$order['id']}",
            'order' => $order,
            'items' => $orders->items((int) $id),
        ]);
    }

    public function cancel(string $id): void
    {
        $user = $this->requireLogin();
        $orders = new Order();
        $order = $orders->find((int) $id);
        if (!$order || (int) $order['user_id'] !== (int) $user['id']) {
            $this->notFound();
        }
        if ($order['status'] !== 'pending') {
            flash('error', 'Only pending orders can be cancelled.');
        } else {
            $orders->cancel((int) $id);
            flash('success', 'Order cancelled.');
        }
        $this->redirect("/orders/$id");
    }
}
