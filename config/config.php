<?php
// Override any value with an environment variable (DB_HOST, DB_NAME, ...).
return [
    'app_name' => 'ShopEasy',
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'b2c_ecommerce',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
        'charset' => 'utf8mb4',
    ],
    'currency'      => '$',
    'per_page'      => 8,
    'shipping_flat' => 5.00,
    'free_shipping_over' => 100.00,
    'tax_rate'      => 0.08,
];
