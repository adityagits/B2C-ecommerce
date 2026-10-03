<?php

class AdminOrderController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $status = (string) ($_GET['status'] ?? '');
        $this->view('admin/orders/index', [
            'title'  => 'Orders',
            'orders' => (new Order())->adminList($status),
            'status' => $status,
        ], 'layout/admin');
    }

    public function show(string $id): void
    {
        $this->requireAdmin();
        $orders = new Order();
        $order = $orders->find((int) $id) ?? $this->notFound();
        $this->view('admin/orders/show', [
            'title' => "Order #$id",
            'order' => $order,
            'items' => $orders->items((int) $id),
        ], 'layout/admin');
    }

    public function updateStatus(string $id): void
    {
        $this->requireAdmin();
        $orders = new Order();
        $order = $orders->find((int) $id) ?? $this->notFound();
        $status = $this->input('status');
        if (!in_array($status, Order::STATUSES, true)) {
            flash('error', 'Invalid status.');
        } elseif ($status === 'cancelled' && $order['status'] !== 'cancelled') {
            $orders->cancel((int) $id);
            flash('success', 'Order cancelled and stock restored.');
        } elseif ($order['status'] === 'cancelled') {
            flash('error', 'Cancelled orders cannot be reopened.');
        } else {
            $orders->setStatus((int) $id, $status);
            flash('success', 'Order status updated.');
        }
        $this->redirect("/admin/orders/$id");
    }
}
