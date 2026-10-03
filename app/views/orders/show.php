<h1>Order #<?= (int) $order['id'] ?> <span class="tag tag-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></h1>
<p class="muted">Placed <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?> · Payment: <?= e($order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Credit card (demo)') ?></p>
<div class="cart-layout">
    <table class="table">
        <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr><td><?= e($i['name']) ?></td><td><?= money($i['price']) ?></td><td><?= (int) $i['quantity'] ?></td><td><?= money($i['price'] * $i['quantity']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <aside class="card summary">
        <h3>Ship to</h3>
        <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?> <?= e($order['shipping_zip']) ?><br><?= e($order['shipping_phone']) ?></p>
        <dl>
            <dt>Subtotal</dt><dd><?= money($order['subtotal']) ?></dd>
            <dt>Shipping</dt><dd><?= money($order['shipping']) ?></dd>
            <dt>Tax</dt><dd><?= money($order['tax']) ?></dd>
            <dt class="total">Total</dt><dd class="total"><?= money($order['total']) ?></dd>
        </dl>
        <?php if ($order['status'] === 'pending' && (int) $order['user_id'] === (int) auth_user()['id']): ?>
            <form action="<?= url('orders/' . (int) $order['id'] . '/cancel') ?>" method="post" onsubmit="return confirm('Cancel this order?')">
                <?= csrf_field() ?><button class="btn btn-danger btn-block" type="submit">Cancel order</button>
            </form>
        <?php endif; ?>
    </aside>
</div>
