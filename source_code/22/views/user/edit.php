<?php if (! $user['verified']): ?>
    <form method="POST" action="verification-email/send">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button>Verify Email</button>
    </form>
<?php else: ?>
<form method="POST" action="/profile">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><br>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly><br>
    <input type="password" name="password" placeholder="Password"><br>
    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error[0]) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <button>Update Profile</button>
</form>
<?php endif; ?>
