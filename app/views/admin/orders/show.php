<h1>Order #<?= (int) $order['id'] ?></h1>
<p class="muted"><?= e($order['customer_name']) ?> (<?= e($order['customer_email']) ?>) · <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>
<div class="cart-layout">
    <table class="table">
        <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr><td><?= e($i['name']) ?></td><td><?= money($i['price']) ?></td><td><?= (int) $i['quantity'] ?></td><td><?= money($i['price'] * $i['quantity']) ?></td></tr>
        <?php endforeach; ?>
        <tr><td colspan="3"><strong>Total</strong> (incl. shipping <?= money($order['shipping']) ?>, tax <?= money($order['tax']) ?>)</td><td><strong><?= money($order['total']) ?></strong></td></tr>
        </tbody>
    </table>
    <aside class="card summary">
        <h3>Ship to</h3>
        <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?> <?= e($order['shipping_zip']) ?><br><?= e($order['shipping_phone']) ?></p>
        <form class="form" action="<?= url('admin/orders/' . (int) $order['id'] . '/status') ?>" method="post">
            <?= csrf_field() ?>
            <label>Status
                <select name="status">
                    <?php foreach (Order::STATUSES as $s): ?>
                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-block" type="submit">Update status</button>
        </form>
    </aside>
</div>
<p><a href="<?= url('admin/orders') ?>">← All orders</a></p>
