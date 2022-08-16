<?php

use Faker\Generator as Faker;

$factory->define(
    App\Models\Task::class,
    function (Faker $faker) {
        return [
            'due_date'   => $faker->date(),
            'is_private' => 0,
            'is_visible' => 1,
            'status'     => 1,
            'title'      => $faker->word,
            'text'       => $faker->sentence,
            'time_mark'  => 'new'
        ];
    }
);