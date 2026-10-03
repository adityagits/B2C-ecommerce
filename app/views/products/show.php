<div class="product-detail">
    <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>">
    <div>
        <?php if ($product['category_name']): ?>
            <a class="muted small" href="<?= url('products?category=' . (int) $product['category_id']) ?>"><?= e($product['category_name']) ?></a>
        <?php endif; ?>
        <h1><?= e($product['name']) ?></h1>
        <?php if ($product['review_count']): ?>
            <div class="stars" title="<?= e($product['avg_rating']) ?> / 5"><?= str_repeat('★', (int) round($product['avg_rating'])) . str_repeat('☆', 5 - (int) round($product['avg_rating'])) ?>
                <span class="muted small"><?= e($product['avg_rating']) ?> (<?= (int) $product['review_count'] ?> reviews)</span></div>
        <?php endif; ?>
        <div class="price big"><?= money($product['price']) ?></div>
        <p><?= nl2br(e($product['description'])) ?></p>

        <?php if ($product['stock'] > 0): ?>
            <p class="<?= $product['stock'] <= 5 ? 'warn' : 'ok' ?>"><?= $product['stock'] <= 5 ? "Only {$product['stock']} left!" : 'In stock' ?></p>
            <form action="<?= url('cart/add') ?>" method="post" class="row">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>" class="qty">
                <button class="btn" type="submit">Add to cart</button>
                <button class="btn btn-outline" type="submit" name="buy_now" value="1">Buy now</button>
            </form>
        <?php else: ?>
            <p><span class="tag tag-out">Out of stock</span></p>
        <?php endif; ?>
    </div>
</div>

<section id="reviews">
    <h2>Customer reviews</h2>
    <?php foreach ($reviews as $r): ?>
        <div class="review">
            <strong><?= e($r['user_name']) ?></strong>
            <span class="stars"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></span>
            <span class="muted small"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></span>
            <?php if ($r['comment']): ?><p><?= nl2br(e($r['comment'])) ?></p><?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (!$reviews): ?><p class="muted">No reviews yet.</p><?php endif; ?>

    <?php if (auth_user()): ?>
        <form class="form" action="<?= url('products/' . (int) $product['id'] . '/review') ?>" method="post">
            <?= csrf_field() ?>
            <h3>Write a review</h3>
            <label>Rating
                <select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option><?php endfor; ?></select>
            </label>
            <label>Comment <textarea name="comment" rows="3" maxlength="1000"></textarea></label>
            <button class="btn" type="submit">Submit review</button>
        </form>
    <?php else: ?>
        <p><a href="<?= url('login') ?>">Log in</a> to write a review.</p>
    <?php endif; ?>
</section>
