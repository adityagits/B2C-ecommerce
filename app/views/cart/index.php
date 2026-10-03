<h1>Shopping cart</h1>
<?php if (!$lines): ?>
    <p class="empty">Your cart is empty. <a href="<?= url('products') ?>">Continue shopping</a></p>
<?php else: ?>
<div class="cart-layout">
    <form action="<?= url('cart/update') ?>" method="post">
        <?= csrf_field() ?>
        <table class="table">
            <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($lines as $l): $p = $l['product']; ?>
                <tr>
                    <td class="cell-product"><img src="<?= e(product_image($p['image'])) ?>" alt=""><a href="<?= url('products/' . $p['id']) ?>"><?= e($p['name']) ?></a></td>
                    <td><?= money($p['price']) ?></td>
                    <td><input class="qty" type="number" name="qty[<?= (int) $p['id'] ?>]" value="<?= $l['qty'] ?>" min="0" max="<?= (int) $p['stock'] ?>"></td>
                    <td><?= money($l['subtotal']) ?></td>
                    <td><button class="link danger" type="submit" form="rm-<?= (int) $p['id'] ?>">Remove</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button class="btn btn-outline" type="submit">Update cart</button>
    </form>
    <?php foreach ($lines as $l): ?>
        <form id="rm-<?= (int) $l['product']['id'] ?>" action="<?= url('cart/remove') ?>" method="post" hidden>
            <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int) $l['product']['id'] ?>">
        </form>
    <?php endforeach; ?>

    <?php include APP . '/views/cart/_summary.php'; ?>
</div>
<?php endif; ?>
