<?php

use App\User;
use App\Project;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(4),
        'description' => $faker->sentence(4),
        'notes' => 'Foobar notes',
        'owner_id' => factory(User::class)
    ];
});
