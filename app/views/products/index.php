<?php
$qs = fn(array $over) => url('products?' . http_build_query(array_filter(
    array_merge(['q' => $q, 'category' => $cat, 'sort' => $sort === 'newest' ? null : $sort], $over),
    fn($v) => $v !== null && $v !== ''
)));
?>
<div class="shop-layout">
    <aside class="filters">
        <h3>Categories</h3>
        <ul class="plain">
            <li><a class="<?= !$cat ? 'active' : '' ?>" href="<?= $qs(['category' => null, 'page' => null]) ?>">All products</a></li>
            <?php foreach ($categories as $c): ?>
                <li><a class="<?= $cat === (int) $c['id'] ? 'active' : '' ?>" href="<?= $qs(['category' => $c['id'], 'page' => null]) ?>"><?= e($c['name']) ?> (<?= (int) $c['product_count'] ?>)</a></li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <section>
        <div class="toolbar">
            <div><strong><?= (int) $total ?></strong> product<?= $total === 1 ? '' : 's' ?><?= $q !== '' ? ' for “' . e($q) . '”' : '' ?></div>
            <form method="get" action="<?= url('products') ?>">
                <input type="hidden" name="q" value="<?= e($q) ?>">
                <?php if ($cat): ?><input type="hidden" name="category" value="<?= (int) $cat ?>"><?php endif; ?>
                <select name="sort" onchange="this.form.submit()">
                    <?php foreach (['newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'name' => 'Name A–Z'] as $k => $label): ?>
                        <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <noscript><button type="submit">Sort</button></noscript>
            </form>
        </div>

        <?php if (!$products): ?>
            <p class="empty">No products found. <a href="<?= url('products') ?>">Clear filters</a></p>
        <?php else: ?>
            <div class="grid"><?php foreach ($products as $p) { include APP . '/views/layout/_product_card.php'; } ?></div>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
            <nav class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= $qs(['page' => $i > 1 ? $i : null]) ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</div>
