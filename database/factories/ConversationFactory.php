<?php

use Faker\Generator as Faker;

$factory->define(
    App\Models\Conversation::class,
    function (Faker $faker) {
        return [
            'title'      => $faker->word,
            'is_visible' => 1
        ];
    }
);