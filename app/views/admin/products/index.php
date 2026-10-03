<div class="row space"><h1>Products</h1><a class="btn" href="<?= url('admin/products/create') ?>">+ New product</a></div>
<table class="table">
    <thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><img class="thumb" src="<?= e(product_image($p['image'])) ?>" alt=""></td>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['category_name'] ?? '—') ?></td>
            <td><?= money($p['price']) ?></td>
            <td class="<?= $p['stock'] <= 5 ? 'warn' : '' ?>"><?= (int) $p['stock'] ?></td>
            <td><?= $p['featured'] ? '★' : '' ?></td>
            <td class="row">
                <a href="<?= url('admin/products/' . $p['id'] . '/edit') ?>">Edit</a>
                <form action="<?= url('admin/products/' . $p['id'] . '/delete') ?>" method="post" class="inline" onsubmit="return confirm('Delete this product?')">
                    <?= csrf_field() ?><button class="link danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
