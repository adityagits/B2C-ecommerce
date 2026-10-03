<h1>Dashboard</h1>
<div class="stats">
    <div class="card stat"><span class="muted">Revenue</span><strong><?= money($stats['revenue']) ?></strong></div>
    <div class="card stat"><span class="muted">Orders</span><strong><?= $stats['orders'] ?></strong></div>
    <div class="card stat"><span class="muted">Pending</span><strong><?= $stats['pending'] ?></strong></div>
    <div class="card stat"><span class="muted">To ship</span><strong><?= $stats['to_ship'] ?></strong></div>
    <div class="card stat"><span class="muted">Products</span><strong><?= $stats['products'] ?></strong></div>
    <div class="card stat"><span class="muted">Customers</span><strong><?= $stats['customers'] ?></strong></div>
</div>
<h2>Recent orders</h2>
<table class="table">
    <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($recent as $o): ?>
        <tr><td>#<?= (int) $o['id'] ?></td><td><?= e($o['customer_name']) ?></td><td><?= status_tag($o['status']) ?></td><td><?= money($o['total']) ?></td><td><a href="<?= url('admin/orders/' . $o['id']) ?>">Manage</a></td></tr>
    <?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="5" class="muted">No orders yet.</td></tr><?php endif; ?>
    </tbody>
</table>
<h2>Low stock</h2>
<ul class="plain">
    <?php foreach ($lowStock as $p): ?>
        <li><a href="<?= url('admin/products/' . $p['id'] . '/edit') ?>"><?= e($p['name']) ?></a> — <span class="warn"><?= (int) $p['stock'] ?> left</span></li>
    <?php endforeach; ?>
    <?php if (!$lowStock): ?><li class="muted">All products well stocked.</li><?php endif; ?>
</ul>
