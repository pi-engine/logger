<?php

return [
    'admin' => [
        [
            'module'      => 'content',
            'section'     => 'admin',
            'package'     => 'item',
            'handler'     => 'list',
            'permissions' => 'item-list',
            'role'        => [
                'admin',
            ],
        ],
        [
            'module'      => 'content',
            'section'     => 'admin',
            'package'     => 'item',
            'handler'     => 'detail',
            'permissions' => 'item-detail',
            'role'        => [
                'admin',
            ],
        ],
        [
            'module'      => 'content',
            'section'     => 'admin',
            'package'     => 'item',
            'handler'     => 'add',
            'permissions' => 'item-add',
            'role'        => [
                'admin',
            ],
        ],
        [
            'module'      => 'content',
            'section'     => 'admin',
            'package'     => 'item',
            'handler'     => 'edit',
            'permissions' => 'item-edit',
            'role'        => [
                'admin',
            ],
        ],
        [
            'module'      => 'content',
            'section'     => 'admin',
            'package'     => 'item',
            'handler'     => 'delete',
            'permissions' => 'item-delete',
            'role'        => [
                'admin',
            ],
        ],
    ],
];