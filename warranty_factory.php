<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Warranty_model::class, function (Faker\Generator $faker) {

    $purchase_date = $faker->dateTimeThisDecade();

    return [
      'purchase_date' => $purchase_date->format('Y-m-d'),
      'end_date' => date_add($purchase_date, date_interval_create_from_date_string('+4 years'))->format('Y-m-d'),
      'status' => $faker->randomElement([
        'Expired',
        'Supported',
        'Can\'t lookup warranty',
        'No Applecare',
        'Unregistered serialnumber',
      ]),
    ];
});
