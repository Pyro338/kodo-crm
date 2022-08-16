<?php

use Faker\Generator as Faker;

$factory->define(
    App\Models\Project::class,
    function (Faker $faker) {
        return [
            'status' => 1,
            'title'  => $faker->word,
            'text'   => $faker->sentence,
            'alias'  => ''
        ];
    }
);