<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Shop') ?> · <?= e(config('app_name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-row">
        <a class="logo" href="<?= url('/') ?>">🛍️ <?= e(config('app_name')) ?></a>
        <form class="search" action="<?= url('products') ?>" method="get">
            <input type="search" name="q" placeholder="Search products…" value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>
        <nav class="nav">
            <a href="<?= url('products') ?>">Shop</a>
            <?php if (auth_user()): ?>
                <?php if (is_admin()): ?><a href="<?= url('admin') ?>">Admin</a><?php endif; ?>
                <a href="<?= url('orders') ?>">My Orders</a>
                <form action="<?= url('logout') ?>" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button class="link" type="submit">Logout (<?= e(auth_user()['name']) ?>)</button>
                </form>
            <?php else: ?>
                <a href="<?= url('login') ?>">Log in</a>
                <a href="<?= url('register') ?>">Sign up</a>
            <?php endif; ?>
            <a class="cart-link" href="<?= url('cart') ?>">🛒 Cart <span class="badge"><?= Cart::count() ?></span></a>
        </nav>
    </div>
</header>

<main class="container">
    <?php foreach ($flashes as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container">&copy; <?= date('Y') ?> <?= e(config('app_name')) ?>. Built with PHP &amp; MySQL.</div>
</footer>
</body>
</html>
