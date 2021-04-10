<?php
/*
 * Copyright (c) 2021.
 * Marc Concepcion
 * marcanthonyconcepcion@gmail.com
 */

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Subscriber;


class SubscribersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Subscriber::truncate();
        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 10; $i++)
        {
            Subscriber::create([
                'email_address' => $faker->email,
                'first_name' => $faker->name,
                'last_name' => $faker->lastName,
                'activation_flag' => $faker->boolean,
            ]);
        }
    }
}
