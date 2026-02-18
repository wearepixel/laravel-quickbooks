<?php

namespace Wearepixel\QuickBooks;

use Wearepixel\QuickBooks\Stubs\User;

function config(string $key): array
{
    return [
        'keys' => [
            'foreign' => 'user_id',
            'owner' => 'id',
        ],
        'model' => User::class,
    ];
}

function route(string $name): string
{
    return $name;
}

function dir(string $path): bool
{
    return $path === '/some/valid/path';
}
