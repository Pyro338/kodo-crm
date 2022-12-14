<?php

use Faker\Generator as Faker;

$factory->define(
    App\Models\Comment::class,
    function (Faker $faker) {
        return [
            'type'      => 'comment',
            'text'      => $faker->sentence,
            'is_arhive' => 0
        ];
    }
);