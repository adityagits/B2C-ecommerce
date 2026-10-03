<h1>Key management</h1>

<?php if ($newKey): ?>
    <div class="alert alert-success">
        <strong>Your new API key</strong> — copy it now, it won't be shown again:
        <pre class="keybox"><?= e($newKey) ?></pre>
    </div>
<?php endif; ?>

<h2>API keys</h2>
<p class="muted">Keys grant read-only access to the JSON API (<code>/api/products</code>, <code>/api/orders</code>). Send as <code>Authorization: Bearer &lt;key&gt;</code>. Only a hash is stored.</p>
<form class="form flat row" action="<?= url('admin/keys/api') ?>" method="post">
    <?= csrf_field() ?>
    <label>Key name <input type="text" name="name" maxlength="100" placeholder="e.g. Accounting integration" required></label>
    <button class="btn" type="submit">Generate key</button>
</form>
<table class="table">
    <thead><tr><th>Name</th><th>Key</th><th>Created</th><th>Last used</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($apiKeys as $k): ?>
        <tr>
            <td><?= e($k['name']) ?></td>
            <td><code>sk_<?= e($k['key_prefix']) ?>…</code></td>
            <td><?= e(date('M j, Y', strtotime($k['created_at']))) ?></td>
            <td><?= $k['last_used_at'] ? e(date('M j, Y g:i A', strtotime($k['last_used_at']))) : 'Never' ?></td>
            <td><?= $k['revoked_at'] ? '<span class="tag tag-cancelled">Revoked</span>' : '<span class="tag tag-paid">Active</span>' ?></td>
            <td>
                <?php if (!$k['revoked_at']): ?>
                <form action="<?= url('admin/keys/api/' . (int) $k['id'] . '/revoke') ?>" method="post" class="inline" onsubmit="return confirm('Revoke this key? Apps using it will stop working.')">
                    <?= csrf_field() ?><button class="link danger" type="submit">Revoke</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$apiKeys): ?><tr><td colspan="6" class="muted">No API keys yet.</td></tr><?php endif; ?>
    </tbody>
</table>

<h2>Integration credentials</h2>
<p class="muted">Store payment gateway, carrier or SMTP secrets here. Values are encrypted (AES-256-GCM) and never displayed again; saving an existing name replaces its value. Code reads them with <code>Credential::get('NAME')</code>.</p>
<form class="form flat row" action="<?= url('admin/keys/credentials') ?>" method="post" autocomplete="off">
    <?= csrf_field() ?>
    <label>Name <input type="text" name="name" list="cred-names" maxlength="60" placeholder="STRIPE_SECRET_KEY" required></label>
    <datalist id="cred-names">
        <?php foreach (['STRIPE_PUBLISHABLE_KEY', 'STRIPE_SECRET_KEY', 'PAYPAL_CLIENT_ID', 'PAYPAL_SECRET', 'RAZORPAY_KEY_ID', 'RAZORPAY_KEY_SECRET', 'SHIPPING_API_KEY', 'SMTP_PASSWORD'] as $n): ?><option value="<?= $n ?>"><?php endforeach; ?>
    </datalist>
    <label>Value <input type="password" name="value" autocomplete="new-password" required></label>
    <button class="btn" type="submit">Save</button>
</form>
<table class="table">
    <thead><tr><th>Name</th><th>Value</th><th>Updated</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($credentials as $c): ?>
        <tr>
            <td><code><?= e($c['name']) ?></code></td>
            <td>••••••••<?= e($c['hint']) ?></td>
            <td><?= e(date('M j, Y g:i A', strtotime($c['updated_at']))) ?></td>
            <td>
                <form action="<?= url('admin/keys/credentials/' . (int) $c['id'] . '/delete') ?>" method="post" class="inline" onsubmit="return confirm('Delete this credential?')">
                    <?= csrf_field() ?><button class="link danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$credentials): ?><tr><td colspan="4" class="muted">No credentials stored.</td></tr><?php endif; ?>
    </tbody>
</table>
