<aside class="summary card">
    <h3>Order summary</h3>
    <dl>
        <dt>Subtotal</dt><dd><?= money($totals['subtotal']) ?></dd>
        <dt>Shipping</dt><dd><?= $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' ?></dd>
        <dt>Tax</dt><dd><?= money($totals['tax']) ?></dd>
        <dt class="total">Total</dt><dd class="total"><?= money($totals['total']) ?></dd>
    </dl>
    <?php if (($summaryAction ?? 'checkout') === 'checkout'): ?>
        <a class="btn btn-block" href="<?= url('checkout') ?>">Proceed to checkout</a>
    <?php endif; ?>
</aside>
