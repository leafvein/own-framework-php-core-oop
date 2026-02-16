<form method="POST" action="/register">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <input type="text" name="name" placeholder="Name" size="40"><br>
    <input type="email" name="email" placeholder="Email" size="40"><br>
    <input type="password" name="password" placeholder="Password"><br>
    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br>

    <button>Register</button>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error[0]) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</form>
