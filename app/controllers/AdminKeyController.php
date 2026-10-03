<?php

class AdminKeyController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $newKey = $_SESSION['new_api_key'] ?? null;
        unset($_SESSION['new_api_key']); // shown exactly once
        $this->view('admin/keys/index', [
            'title'       => 'Keys',
            'apiKeys'     => (new ApiKey())->list(),
            'credentials' => (new Credential())->list(),
            'newKey'      => $newKey,
        ], 'layout/admin');
    }

    public function createApiKey(): void
    {
        $this->requireAdmin();
        $name = mb_substr($this->input('name'), 0, 100);
        if ($name === '') {
            flash('error', 'Give the key a name so you can recognise it later.');
        } else {
            $_SESSION['new_api_key'] = (new ApiKey())->create($name);
            flash('success', 'API key created. Copy it now — it will not be shown again.');
        }
        $this->redirect('/admin/keys');
    }

    public function revokeApiKey(string $id): void
    {
        $this->requireAdmin();
        (new ApiKey())->revoke((int) $id);
        flash('success', 'API key revoked.');
        $this->redirect('/admin/keys');
    }

    public function saveCredential(): void
    {
        $this->requireAdmin();
        $name = strtoupper($this->input('name'));
        $value = $this->input('value');
        if (!preg_match('/^[A-Z0-9_]{2,60}$/', $name)) {
            flash('error', 'Name may only contain letters, digits and underscores (e.g. STRIPE_SECRET_KEY).');
        } elseif ($value === '') {
            flash('error', 'Value is required.');
        } else {
            (new Credential())->save($name, $value);
            flash('success', "$name saved (encrypted).");
        }
        $this->redirect('/admin/keys');
    }

    public function deleteCredential(string $id): void
    {
        $this->requireAdmin();
        (new Credential())->delete((int) $id);
        flash('success', 'Credential deleted.');
        $this->redirect('/admin/keys');
    }
}
