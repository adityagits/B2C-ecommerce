<form class="form narrow" action="<?= url('register') ?>" method="post">
    <?= csrf_field() ?>
    <h1>Create account</h1>
    <label>Full name <input type="text" name="name" value="<?= e(old('name')) ?>" required autofocus></label>
    <label>Email <input type="email" name="email" value="<?= e(old('email')) ?>" required></label>
    <label>Password <input type="password" name="password" minlength="6" required></label>
    <label>Confirm password <input type="password" name="password_confirm" minlength="6" required></label>
    <button class="btn btn-block" type="submit">Sign up</button>
    <p class="muted">Already registered? <a href="<?= url('login') ?>">Log in</a></p>
</form>
