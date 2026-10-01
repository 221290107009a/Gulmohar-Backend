<?php

return [
    'admin.users' => [
        'index' => 'user::permissions.users.index',
        'create' => 'user::permissions.users.create',
        'edit' => 'user::permissions.users.edit',
        'destroy' => 'user::permissions.users.destroy',
    ],
    'admin.member' => [
        'index' => 'user::permissions.member.index',
        'create' => 'user::permissions.member.create',
        'edit' => 'user::permissions.member.edit',
        'destroy' => 'user::permissions.member.destroy',
    ],
    'admin.roles' => [
        'index' => 'user::permissions.roles.index',
        'create' => 'user::permissions.roles.create',
        'edit' => 'user::permissions.roles.edit',
        'destroy' => 'user::permissions.roles.destroy',
    ],
    'admin.subscription_histories' => [
        'index' => 'user::permissions.subscription_history.index',        
    ]
];
