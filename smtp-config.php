<?php

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'your-email@gmail.com',
        'password' => 'your-app-password',
        'from_email' => 'noreply@yourdomain.com',
        'from_name' => 'Your Company Name',
        'timeout' => 30,
        'debug' => false
    ],

    'email' => [
        'admin_email' => 'admin@yourdomain.com',
        'site_name' => 'Your Website Name',
        'site_url' => 'https://yourdomain.com'
    ]
];