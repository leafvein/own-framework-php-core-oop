<?php
return [
    'host'     => $_ENV['DB_HOST'] ?? 'mysql',
    'database' => $_ENV['DB_NAME'] ?? 'example_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'],
];
