<h1>Payment transactions</h1>
<table class="table">
    <thead><tr><th>Transaction</th><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th>Status</th><th>Paid</th></tr></thead>
    <tbody>
    <?php foreach ($payments as $p): ?>
        <tr>
            <td><?= e($p['transaction_id']) ?></td>
            <td><a href="<?= url('admin/orders/' . (int) $p['order_id']) ?>">#<?= (int) $p['order_id'] ?></a></td>
            <td><?= e($p['customer_name']) ?></td>
            <td><?= e($p['method'] === 'cod' ? 'Cash on delivery' : 'Card') ?></td>
            <td><?= money($p['amount']) ?></td>
            <td><?= status_tag($p['status']) ?></td>
            <td><?= $p['paid_at'] ? e(date('M j, Y g:i A', strtotime($p['paid_at']))) : '—' ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$payments): ?><tr><td colspan="7" class="muted">No payments yet.</td></tr><?php endif; ?>
    </tbody>
</table>
