<form class="form narrow" action="<?= url('login') ?>" method="post">
    <?= csrf_field() ?>
    <h1>Log in</h1>
    <label>Email <input type="email" name="email" value="<?= e(old('email')) ?>" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn btn-block" type="submit">Log in</button>
    <p class="muted">New here? <a href="<?= url('register') ?>">Create an account</a></p>
</form>
