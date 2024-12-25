<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Point\Models\Entities\Setting;
use WalkerChiu\Point\Models\Entities\SettingLang;

$factory->define(Setting::class, function (Faker $faker) {
    return [
        'identifier'    => $faker->slug,
        'exchange_rate' => 1
    ];
});

$factory->define(SettingLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['name', 'description']),
        'value' => $faker->sentence
    ];
});
