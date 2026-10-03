<?php /** @var array $events */ ?>
<h3>Order history</h3>
<ul class="timeline">
    <?php foreach ($events as $ev): ?>
        <li><span class="small muted"><?= e(date('M j, Y g:i A', strtotime($ev['created_at']))) ?></span>
            <?= e($ev['message']) ?><?php if (is_admin() && !empty($ev['user_name'])): ?> <span class="muted small">· <?= e($ev['user_name']) ?></span><?php endif; ?></li>
    <?php endforeach; ?>
</ul>
