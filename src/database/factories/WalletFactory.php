<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Point\Models\Entities\Wallet;

$factory->define(Wallet::class, function (Faker $faker) {
    return [
        'value' => $faker->randomNumber
    ];
});
