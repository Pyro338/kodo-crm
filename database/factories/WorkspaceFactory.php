<?php

use Faker\Generator as Faker;

$factory->define(
    App\Models\Workspace::class,
    function (Faker $faker) {
        return [
            'title'       => $faker->word,
            'description' => $faker->sentence,
            'is_visible'  => 1
        ];
    }
);