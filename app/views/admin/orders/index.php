<h1>Orders</h1>
<div class="chips">
    <a class="chip <?= $status === '' ? 'active' : '' ?>" href="<?= url('admin/orders') ?>">All</a>
    <?php foreach (Order::STATUSES as $s): ?>
        <a class="chip <?= $status === $s ? 'active' : '' ?>" href="<?= url('admin/orders?status=' . $s) ?>"><?= e(ucfirst($s)) ?></a>
    <?php endforeach; ?>
</div>
<table class="table">
    <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Invoice</th><th>Status</th><th>Payment</th><th>Delivery</th><th>Total</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td>#<?= (int) $o['id'] ?></td><td><?= e($o['customer_name']) ?></td>
            <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
            <td><?= e($o['invoice_number']) ?></td>
            <td><?= status_tag($o['status']) ?></td>
            <td><?= status_tag($o['payment_status']) ?></td>
            <td><?= status_tag($o['shipment_status']) ?></td>
            <td><?= money($o['total']) ?></td>
            <td><a href="<?= url('admin/orders/' . $o['id']) ?>">Manage</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?><tr><td colspan="8" class="muted">No orders.</td></tr><?php endif; ?>
    </tbody>
</table>
