<?php
declare(strict_types = 1);

try {
    require __DIR__ . '/../app/Core/Bootstrap.php';
} catch (Throwable $e) {
    echo 'Message: ' . $e->getMessage() . '<br>';
    echo 'File: '    . $e->getFile()    . '<br>';
    echo 'Line: '    . $e->getLine()    . '<br>';
}
