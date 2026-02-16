<?php if (isset($_SESSION['flash_message'])) : ?>
    <div class="flash_message"><?= $_SESSION['flash_message'] ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
<?php if (! $user['verified']): ?>
    <form method="POST" action="verification-email/send">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button>Verify Email</button>
    </form>
<?php else: ?>
    <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
    <p>Your are a verified user!</p>
<?php endif; ?>
