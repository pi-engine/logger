<?php


return [
    'admin' => [
        [
            'module' => 'logger',
            'section' => 'admin',
            'package' => 'inventory',
            'handler' => 'read',
            'permission' => 'admin-logger-inventory-read',
            'role' => [
                'admin',
            ],
        ],
    ],
];