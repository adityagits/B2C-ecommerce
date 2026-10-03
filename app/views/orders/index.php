<h1>My orders</h1>
<?php if (!$orders): ?>
    <p class="empty">You haven't placed any orders yet. <a href="<?= url('products') ?>">Start shopping</a></p>
<?php else: ?>
<table class="table">
    <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Delivery</th><th>Total</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td>#<?= (int) $o['id'] ?></td>
            <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
            <td><?= status_tag($o['status']) ?></td>
            <td><?= status_tag($o['shipment_status']) ?></td>
            <td><?= money($o['total']) ?></td>
            <td><a href="<?= url('orders/' . $o['id']) ?>">View</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
