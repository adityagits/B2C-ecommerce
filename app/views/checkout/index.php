<h1>Checkout</h1>
<div class="cart-layout">
    <form class="form" action="<?= url('checkout') ?>" method="post">
        <?= csrf_field() ?>
        <h3>Shipping details</h3>
        <label>Full name <input type="text" name="name" value="<?= e(old('name', auth_user()['name'])) ?>" required></label>
        <label>Address <input type="text" name="address" value="<?= e(old('address')) ?>" required></label>
        <div class="row">
            <label>City <input type="text" name="city" value="<?= e(old('city')) ?>" required></label>
            <label>ZIP code <input type="text" name="zip" value="<?= e(old('zip')) ?>" required></label>
        </div>
        <label>Phone <input type="tel" name="phone" value="<?= e(old('phone')) ?>" required></label>

        <h3>Payment</h3>
        <?php foreach ($payments as $key => $label): ?>
            <label class="radio"><input type="radio" name="payment" value="<?= e($key) ?>" <?= old('payment', 'cod') === $key ? 'checked' : '' ?>> <?= e($label) ?></label>
        <?php endforeach; ?>
        <p class="muted small">Demo store: no real payment is processed.</p>
        <button class="btn btn-block" type="submit">Place order</button>
    </form>

    <aside>
        <div class="card summary">
            <h3>Your items</h3>
            <ul class="plain items">
                <?php foreach ($lines as $l): ?>
                    <li><span><?= e($l['product']['name']) ?> × <?= $l['qty'] ?></span><span><?= money($l['subtotal']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php $summaryAction = 'none'; include APP . '/views/cart/_summary.php'; ?>
    </aside>
</div>
