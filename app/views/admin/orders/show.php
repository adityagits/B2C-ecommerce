<?php $cancelled = $order['status'] === 'cancelled'; ?>
<div class="row space">
    <h1>Order #<?= (int) $order['id'] ?> <?= status_tag($order['status']) ?></h1>
    <?php if ($invoice): ?><a class="btn btn-outline" href="<?= url('orders/' . (int) $order['id'] . '/invoice') ?>">Invoice <?= e($invoice['invoice_number']) ?> (<?= e(status_label($invoice['status'])) ?>)</a><?php endif; ?>
</div>
<p class="muted"><?= e($order['customer_name']) ?> (<?= e($order['customer_email']) ?>) · <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>

<div class="cart-layout">
    <div>
        <table class="table">
            <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($items as $i): ?>
                <tr><td><?= e($i['name']) ?></td><td><?= money($i['price']) ?></td><td><?= (int) $i['quantity'] ?></td><td><?= money($i['price'] * $i['quantity']) ?></td></tr>
            <?php endforeach; ?>
            <tr><td colspan="3"><strong>Total</strong> (incl. shipping <?= money($order['shipping']) ?>, tax <?= money($order['tax']) ?>)</td><td><strong><?= money($order['total']) ?></strong></td></tr>
            </tbody>
        </table>
        <div class="card pad"><?php include APP . '/views/orders/_timeline.php'; ?></div>
    </div>

    <aside>
        <div class="card summary">
            <h3>Ship to</h3>
            <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?> <?= e($order['shipping_zip']) ?><br><?= e($order['shipping_phone']) ?></p>
        </div>

        <?php if ($payment): ?>
        <div class="card summary">
            <h3>Payment <?= status_tag($payment['status']) ?></h3>
            <p class="small muted"><?= e($payment['method'] === 'cod' ? 'Cash on delivery' : 'Credit card (demo)') ?> · <?= money($payment['amount']) ?><br>Txn <?= e($payment['transaction_id']) ?>
                <?php if ($payment['paid_at']): ?><br>Paid <?= e(date('M j, Y g:i A', strtotime($payment['paid_at']))) ?><?php endif; ?></p>
            <?php if (!$cancelled): ?>
            <form action="<?= url('admin/orders/' . (int) $order['id'] . '/payment') ?>" method="post" class="row">
                <?= csrf_field() ?>
                <?php if (in_array($payment['status'], ['pending', 'failed'], true)): ?><button class="btn" name="action" value="paid">Mark paid</button><?php endif; ?>
                <?php if ($payment['status'] === 'pending'): ?><button class="btn btn-outline" name="action" value="failed">Mark failed</button><?php endif; ?>
                <?php if ($payment['status'] === 'paid'): ?><button class="btn btn-danger" name="action" value="refunded" onclick="return confirm('Refund this payment?')">Refund</button><?php endif; ?>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($shipment): ?>
        <div class="card summary">
            <h3>Delivery <?= status_tag($shipment['status']) ?></h3>
            <?php if (!$cancelled): ?>
            <form class="form flat" action="<?= url('admin/orders/' . (int) $order['id'] . '/shipment') ?>" method="post">
                <?= csrf_field() ?>
                <label>Status
                    <select name="status">
                        <?php foreach (Order::SHIPMENT_STATUSES as $s): ?>
                            <option value="<?= $s ?>" <?= $shipment['status'] === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Carrier <input type="text" name="carrier" maxlength="60" value="<?= e($shipment['carrier']) ?>" placeholder="e.g. DHL, FedEx"></label>
                <label>Tracking number <input type="text" name="tracking" maxlength="100" value="<?= e($shipment['tracking_number']) ?>"></label>
                <label>Estimated delivery <input type="date" name="eta" value="<?= e($shipment['estimated_delivery']) ?>"></label>
                <button class="btn btn-block" type="submit">Save delivery</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!in_array($order['status'], ['cancelled', 'delivered'], true)): ?>
            <form action="<?= url('admin/orders/' . (int) $order['id'] . '/cancel') ?>" method="post" onsubmit="return confirm('Cancel this order? Stock is restored and any payment refunded.')">
                <?= csrf_field() ?><button class="btn btn-danger btn-block" type="submit">Cancel order</button>
            </form>
        <?php endif; ?>
    </aside>
</div>
<p><a href="<?= url('admin/orders') ?>">← All orders</a></p>
