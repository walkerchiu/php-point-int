<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Point\Models\Entities\Record;

$factory->define(Record::class, function (Faker $faker) {
	$value_original = $faker->randomNumber;

    return [
        'value_original' => $value_original,
        'value'          => $value_original
    ];
});
