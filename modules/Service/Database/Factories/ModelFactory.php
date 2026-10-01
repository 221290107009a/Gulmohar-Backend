<?php

use Modules\Service\Entities\Service;

$factory->define(Service::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word(),
        'slug' => $faker->slug(),
        'on_navigation' => $faker->boolean(),
        'is_active' => $faker->boolean(),
    ];
});
