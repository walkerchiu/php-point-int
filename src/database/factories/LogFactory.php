<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Point\Models\Entities\Log;

$factory->define(Log::class, function (Faker $faker) {
	$value_original = $faker->randomNumber;

    return [
        'value_original' => $value_original,
        'value'          => $value_original
    ];
});
