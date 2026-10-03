<?php /** @var array $p */ ?>
<article class="card product-card">
    <a href="<?= url('products/' . $p['id']) ?>"><img src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
    <div class="card-body">
        <?php if (!empty($p['category_name'])): ?><span class="muted small"><?= e($p['category_name']) ?></span><?php endif; ?>
        <h3><a href="<?= url('products/' . $p['id']) ?>"><?= e($p['name']) ?></a></h3>
        <div class="price"><?= money($p['price']) ?></div>
        <?php if ($p['stock'] > 0): ?>
            <form action="<?= url('cart/add') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                <button class="btn btn-block" type="submit">Add to cart</button>
            </form>
        <?php else: ?>
            <span class="tag tag-out">Out of stock</span>
        <?php endif; ?>
    </div>
</article>
