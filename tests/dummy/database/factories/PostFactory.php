<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use DummyApp\Post;
use DummyApp\User;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'author_id' => factory(User::class),
        'content' => $faker->text,
        'synopsis' => $faker->sentence,
        'title' => $faker->words(3, true),
    ];
});
