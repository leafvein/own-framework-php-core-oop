<?php if (isset($_SESSION['flash_message'])) : ?>
    <div class="flash_message"><?= $_SESSION['flash_message'] ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
<form method="POST" action="/login">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="email" name="email" placeholder="Email"><br>
    <input type="password" name="password" placeholder="Password"><br>
    
    <button>Login</button>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error[0]) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</form>
