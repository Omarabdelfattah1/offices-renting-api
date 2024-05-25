<?php
namespace Database\Seeders\Roles;

class AllPermissions{
    public static $permissions = [
        'roles' => [
            'show role',
            'create role',
            'update role',
            'delete role',
            'assign role',
        ],
        'users' => [
            'show user',
            'create user',
            'update user',
            'delete user'
        ],
        'offices' => [
            'show office',
            'create office',
            'update office',
            'delete office'
        ],
        'reservations' => [
            'show reservation',
            'create reservation',
            'update reservation',
            'delete reservation'
        ],
        'tags' => [
            'show tag',
            'create tag',
            'update tag',
            'delete tag'
        ],
    ];
}
