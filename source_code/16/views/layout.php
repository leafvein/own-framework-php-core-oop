<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $title ?? 'My OOP App' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<header><h1>My OOP App</h1></header>
<main>
<?= $content ?>
</main>
<footer>Copyright &copy; <?= date('Y') ?></footer>
<script src="<?= asset('scripts/app.js') ?>"></script>
</body>
</html>
