<?php $co = config('company'); ?>
<div class="invoice">
    <div class="inv-head">
        <div>
            <h1><?= e($co['name']) ?></h1>
            <div class="muted"><?= e($co['address']) ?><br><?= e($co['email']) ?><br>Tax ID: <?= e($co['tax_id']) ?></div>
        </div>
        <div class="inv-meta">
            <h2>INVOICE</h2>
            <div><strong><?= e($invoice['invoice_number']) ?></strong></div>
            <div class="muted">Issued <?= e(date('M j, Y', strtotime($invoice['issued_at']))) ?></div>
            <div><?= status_tag($invoice['status']) ?></div>
        </div>
    </div>

    <div class="inv-parties">
        <div><div class="muted small">BILL TO</div><strong><?= e($invoice['billing_name']) ?></strong><br><?= e($invoice['billing_address']) ?><br><?= e($order['customer_email']) ?></div>
        <div><div class="muted small">SHIP TO</div><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?> <?= e($order['shipping_zip']) ?></div>
        <div><div class="muted small">ORDER</div>#<?= (int) $order['id'] ?><br><?= e(date('M j, Y', strtotime($order['created_at']))) ?><?php if ($payment): ?><br><span class="muted small">Txn <?= e($payment['transaction_id']) ?></span><?php endif; ?></div>
    </div>

    <table class="table">
        <thead><tr><th>Description</th><th>Unit price</th><th>Qty</th><th>Amount</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr><td><?= e($i['name']) ?></td><td><?= money($i['price']) ?></td><td><?= (int) $i['quantity'] ?></td><td><?= money($i['price'] * $i['quantity']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <dl class="inv-totals">
        <dt>Subtotal</dt><dd><?= money($invoice['subtotal']) ?></dd>
        <dt>Shipping</dt><dd><?= money($invoice['shipping']) ?></dd>
        <dt>Tax</dt><dd><?= money($invoice['tax']) ?></dd>
        <dt class="total">Total</dt><dd class="total"><?= money($invoice['total']) ?></dd>
        <?php if ($payment): ?>
            <dt>Payment (<?= e($payment['method'] === 'cod' ? 'Cash on delivery' : 'Card') ?>)</dt><dd><?= e(status_label($payment['status'])) ?></dd>
        <?php endif; ?>
    </dl>
    <p class="muted small">Thank you for your business.</p>
</div>
