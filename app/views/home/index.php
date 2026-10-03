<section class="hero">
    <h1>Everything you need, delivered.</h1>
    <p>Discover great products across electronics, fashion, home and more.</p>
    <a class="btn btn-light" href="<?= url('products') ?>">Shop now</a>
</section>

<h2>Shop by category</h2>
<div class="chips">
    <?php foreach ($categories as $c): ?>
        <a class="chip" href="<?= url('products?category=' . $c['id']) ?>"><?= e($c['name']) ?> <span class="muted">(<?= (int) $c['product_count'] ?>)</span></a>
    <?php endforeach; ?>
</div>

<?php if ($featured): ?>
    <h2>Featured</h2>
    <div class="grid"><?php foreach ($featured as $p) { include APP . '/views/layout/_product_card.php'; } ?></div>
<?php endif; ?>

<h2>New arrivals</h2>
<div class="grid"><?php foreach ($latest as $p) { include APP . '/views/layout/_product_card.php'; } ?></div>
