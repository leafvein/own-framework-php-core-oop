<?php
return [
    'name'  => $_ENV['APP_NAME'] ?? 'My OOP App',
    'url' => ($_ENV['APP_URL'] ?? 'http://localhost:8000'),
    'env'   => $_ENV['APP_ENV'] ?? 'local',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
];
