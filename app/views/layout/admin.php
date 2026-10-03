<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin · <?= e($title ?? '') ?> · <?= e(config('app_name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="admin">
<div class="admin-wrap">
    <aside class="admin-side">
        <a class="logo" href="<?= url('admin') ?>">⚙️ Admin</a>
        <a href="<?= url('admin') ?>">Dashboard</a>
        <a href="<?= url('admin/products') ?>">Products</a>
        <a href="<?= url('admin/orders') ?>">Orders</a>
        <a href="<?= url('admin/invoices') ?>">Invoices</a>
        <a href="<?= url('admin/payments') ?>">Payments</a>
        <a href="<?= url('admin/keys') ?>">Keys</a>
        <a href="<?= url('/') ?>">← View store</a>
        <form action="<?= url('logout') ?>" method="post"><?= csrf_field() ?><button class="link" type="submit">Logout</button></form>
    </aside>
    <section class="admin-main">
        <?php foreach ($flashes as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </section>
</div>
</body>
</html>
