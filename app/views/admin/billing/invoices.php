<h1>Invoices</h1>
<table class="table">
    <thead><tr><th>Invoice</th><th>Order</th><th>Customer</th><th>Issued</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($invoices as $i): ?>
        <tr>
            <td><?= e($i['invoice_number']) ?></td>
            <td><a href="<?= url('admin/orders/' . (int) $i['order_id']) ?>">#<?= (int) $i['order_id'] ?></a></td>
            <td><?= e($i['customer_name']) ?></td>
            <td><?= e(date('M j, Y', strtotime($i['issued_at']))) ?></td>
            <td><?= money($i['total']) ?></td>
            <td><?= status_tag($i['status']) ?></td>
            <td><a href="<?= url('orders/' . (int) $i['order_id'] . '/invoice') ?>">Open</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$invoices): ?><tr><td colspan="7" class="muted">No invoices yet.</td></tr><?php endif; ?>
    </tbody>
</table>
