<?php

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (auth_user()) {
            $this->redirect('/');
        }
        $this->view('auth/login', ['title' => 'Log in']);
    }

    public function login(): void
    {
        $email = strtolower($this->input('email'));
        $user = (new User())->findByEmail($email);

        if (!$user || !password_verify($this->input('password'), $user['password'])) {
            $this->withOld(['email']);
            flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];

        $to = $_SESSION['intended'] ?? ($user['role'] === 'admin' ? '/admin' : '/');
        unset($_SESSION['intended']);
        flash('success', "Welcome back, {$user['name']}!");
        $this->redirect($to);
    }

    public function registerForm(): void
    {
        if (auth_user()) {
            $this->redirect('/');
        }
        $this->view('auth/register', ['title' => 'Create account']);
    }

    public function register(): void
    {
        $name = $this->input('name');
        $email = strtolower($this->input('email'));
        $password = (string) ($_POST['password'] ?? '');
        $errors = [];

        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== ($_POST['password_confirm'] ?? '')) $errors[] = 'Passwords do not match.';

        $users = new User();
        if (!$errors && $users->findByEmail($email)) {
            $errors[] = 'That email is already registered.';
        }
        if ($errors) {
            $this->withOld(['name', 'email']);
            foreach ($errors as $err) flash('error', $err);
            $this->redirect('/register');
        }

        $id = $users->create($name, $email, $password);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => 'customer'];
        flash('success', 'Account created. Welcome!');
        $this->redirect('/');
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        flash('info', 'You have been logged out.');
        $this->redirect('/');
    }
}
