<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

// Create an instance of the ExceptionHandler and register it
$exceptionHandler = new \App\Core\ExceptionHandler();
$exceptionHandler->register();

return new \App\Core\App();
