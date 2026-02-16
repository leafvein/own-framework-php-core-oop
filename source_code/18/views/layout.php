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
        <main>
        <?= $content ?>
        </main>
        <footer>Copyright &copy; <?= date('Y') ?></footer>
    </div>
<script src="<?= asset('scripts/app.js') ?>"></script>
</body>
</html>
