<?php $editing = (bool) $product; $v = fn($k, $d = '') => old($k, $product[$k] ?? $d); ?>
<h1><?= $editing ? 'Edit product' : 'New product' ?></h1>
<form class="form" method="post" enctype="multipart/form-data"
      action="<?= url($editing ? 'admin/products/' . (int) $product['id'] : 'admin/products') ?>">
    <?= csrf_field() ?>
    <label>Name <input type="text" name="name" value="<?= e($v('name')) ?>" required></label>
    <label>Description <textarea name="description" rows="5"><?= e($v('description')) ?></textarea></label>
    <label>Category
        <select name="category_id">
            <option value="">— none —</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (string) $v('category_id') === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="row">
        <label>Price <input type="number" name="price" step="0.01" min="0" value="<?= e($v('price')) ?>" required></label>
        <label>Stock <input type="number" name="stock" min="0" value="<?= e($v('stock', 0)) ?>" required></label>
    </div>
    <label>Image <input type="file" name="image" accept="image/*"></label>
    <?php if ($editing && $product['image']): ?><img class="thumb" src="<?= e(product_image($product['image'])) ?>" alt=""><?php endif; ?>
    <label class="radio"><input type="checkbox" name="featured" value="1" <?= $v('featured') ? 'checked' : '' ?>> Featured on home page</label>
    <div class="row">
        <button class="btn" type="submit">Save</button>
        <a class="btn btn-outline" href="<?= url('admin/products') ?>">Cancel</a>
    </div>
</form>
