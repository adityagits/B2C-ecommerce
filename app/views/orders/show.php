<div class="row space">
    <h1>Order #<?= (int) $order['id'] ?> <?= status_tag($order['status']) ?></h1>
    <?php if ($invoice && $invoice['status'] !== 'void'): ?>
        <a class="btn btn-outline" href="<?= url('orders/' . (int) $order['id'] . '/invoice') ?>">View invoice <?= e($invoice['invoice_number']) ?></a>
    <?php endif; ?>
</div>
<p class="muted">Placed <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>
<div class="cart-layout">
    <div>
        <table class="table">
            <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($items as $i): ?>
                <tr><td><?= e($i['name']) ?></td><td><?= money($i['price']) ?></td><td><?= (int) $i['quantity'] ?></td><td><?= money($i['price'] * $i['quantity']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($shipment): ?><div class="card pad"><?php include APP . '/views/orders/_tracking.php'; ?></div><?php endif; ?>
        <div class="card pad"><?php include APP . '/views/orders/_timeline.php'; ?></div>
    </div>
    <aside class="card summary">
        <h3>Ship to</h3>
        <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?> <?= e($order['shipping_zip']) ?><br><?= e($order['shipping_phone']) ?></p>
        <h3>Payment</h3>
        <?php if ($payment): ?>
            <p><?= e($payment['method'] === 'cod' ? 'Cash on delivery' : 'Credit card (demo)') ?> <?= status_tag($payment['status']) ?><br>
               <span class="muted small">Txn <?= e($payment['transaction_id']) ?></span></p>
        <?php endif; ?>
        <dl>
            <dt>Subtotal</dt><dd><?= money($order['subtotal']) ?></dd>
            <dt>Shipping</dt><dd><?= money($order['shipping']) ?></dd>
            <dt>Tax</dt><dd><?= money($order['tax']) ?></dd>
            <dt class="total">Total</dt><dd class="total"><?= money($order['total']) ?></dd>
        </dl>
        <?php if (in_array($order['status'], ['pending', 'paid'], true) && (int) $order['user_id'] === (int) auth_user()['id']): ?>
            <form action="<?= url('orders/' . (int) $order['id'] . '/cancel') ?>" method="post" onsubmit="return confirm('Cancel this order?')">
                <?= csrf_field() ?><button class="btn btn-danger btn-block" type="submit">Cancel order</button>
            </form>
        <?php endif; ?>
    </aside>
</div>
