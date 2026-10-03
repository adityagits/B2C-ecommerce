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
            'title'    => "Order #$id",
            'order'    => $order,
            'items'    => $orders->items((int) $id),
            'invoice'  => $orders->invoice((int) $id),
            'payment'  => $orders->payment((int) $id),
            'shipment' => $orders->shipment((int) $id),
            'events'   => $orders->events((int) $id),
        ], 'layout/admin');
    }

    public function updatePayment(string $id): void
    {
        $this->requireAdmin();
        $this->attempt($id, 'Payment updated.', fn(Order $o) => $o->setPayment((int) $id, $this->input('action')));
    }

    public function updateShipment(string $id): void
    {
        $this->requireAdmin();
        $this->attempt($id, 'Shipment updated.', fn(Order $o) => $o->updateShipment((int) $id, [
            'carrier'  => mb_substr($this->input('carrier'), 0, 60),
            'tracking' => mb_substr($this->input('tracking'), 0, 100),
            'status'   => $this->input('status'),
            'eta'      => $this->input('eta') ?: null,
        ]));
    }

    public function cancel(string $id): void
    {
        $this->requireAdmin();
        $this->attempt($id, 'Order cancelled, stock restored and payment settled.', function (Order $o) use ($id) {
            $order = $o->find((int) $id);
            if (in_array($order['status'], ['cancelled', 'delivered'], true)) {
                throw new RuntimeException('This order can no longer be cancelled.');
            }
            $o->cancel((int) $id);
        });
    }

    private function attempt(string $id, string $success, callable $action): never
    {
        $orders = new Order();
        $orders->find((int) $id) ?? $this->notFound();
        try {
            $action($orders);
            flash('success', $success);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        $this->redirect("/admin/orders/$id");
    }
}
