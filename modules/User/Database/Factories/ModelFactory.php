<?php

use Faker\Generator;
use Modules\User\Entities\User;

$factory->define(User::class, function (Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->safeEmail,
        'password' => bcrypt($faker->password(12, 32)),
    ];
});
