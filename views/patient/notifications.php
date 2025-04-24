<h4>Notifications</h4>
<?php if (!empty($notifications)) : ?>
    <ul>
        <?php foreach ($notifications as $notification) : ?>
            <li>
                <?= htmlspecialchars($notification['message']) ?>
                <small>(<?= htmlspecialchars($notification['created_at']) ?>)</small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <p>No new notifications.</p>
<?php endif; ?>