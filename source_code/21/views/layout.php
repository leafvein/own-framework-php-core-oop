<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $title ?? 'My OOP App' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <div class="content-wrapper">
        <header><h1>My OOP App</h1></header>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dashboard">Dashboard</a> |
                <a href="/profile">Profile</a> |
                <form action="/logout" method="POST" class="logout-form">
                    <button type="submit" class="logout-link">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login">Login</a> |
                <a href="/register">Register</a> |
            <?php endif; ?>
        </nav>
        <main>
        <?= $content ?>
        </main>
        <footer>Copyright &copy; <?= date('Y') ?></footer>
    </div>
<script src="<?= asset('scripts/app.js') ?>"></script>
</body>
</html>
