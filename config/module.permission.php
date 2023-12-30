<?php


return [
    'admin' => [
        [
            'title'      => 'Admin logger user read',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'user',
            'handler'    => 'read',
            'permission' => 'admin-logger-user-read',
            'role'       => [
                'admin',
            ],
        ],
        [
            'title'      => 'Admin logger inventory read',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'inventory',
            'handler'    => 'read',
            'permission' => 'admin-logger-inventory-read',
            'role'       => [
                'admin',
            ],
        ],
    ],
];