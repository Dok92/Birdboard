<?php

use App\Task;
use App\Project;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'body' => $faker->sentence(),
        'project_id' => factory(Project::class),
        'completed' => false
    ];
});
