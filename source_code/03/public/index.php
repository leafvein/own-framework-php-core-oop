<?php
declare(strict_types = 1);

// error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo 'Message: ' . $errno   . ' - ' . $errstr . '<br>';
    echo 'File: '    . $errfile . '<br>';
    echo 'Line: '    . $errline . '<br>';
});

// exception handler
set_exception_handler(function (Exception $e) {
    echo 'Message: ' . $e->getMessage() . '<br>';
    echo 'File: '    . $e->getFile()    . '<br>';
    echo 'Line: '    . $e->getLine()    . '<br>';
});

require __DIR__ . '/../app/Core/Bootstrap.php';
