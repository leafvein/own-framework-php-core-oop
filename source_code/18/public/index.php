<?php
declare(strict_types = 1);

$app = require __DIR__ . '/../app/Core/Bootstrap.php';

session_start();

$app->run();
