<?php
return [
    'mail_host'      => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
    'mail_port'      => $_ENV['MAIL_PORT'] ?? '2525',
    'mail_username'  => $_ENV['MAIL_USERNAME'],
    'mail_password'  => $_ENV['MAIL_PASSWORD'],
    'mail_from'      => $_ENV['MAIL_FROM'] ?? 'your@gmail.com',
    'mail_from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'App Name',
];
