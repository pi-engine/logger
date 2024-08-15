<?php


return [
    'admin' => [
        [
            'title'      => 'Admin logger user read',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'system',
            'handler'    => 'read',
            'permission' => 'admin-logger-system-list',
            'role'       => [
                'admin',
            ],
        ],
        [
            'title'      => 'Admin logger user list',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'user',
            'handler'    => 'list',
            'permission' => 'admin-logger-user-list',
            'role'       => [
                'admin',
            ],
        ],
    ],
];