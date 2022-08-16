<?php

use Faker\Generator as Faker;

$factory->define(
    App\Models\Follower::class,
    function (Faker $faker) {
        return [
            'type'      => 'task'
        ];
    }
);