<?php
/*
 * Copyright (c) 2021.
 * Marc Concepcion
 * marcanthonyconcepcion@gmail.com
 */

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;


class SubscriberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected string $model = Subscriber::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    #[ArrayShape(['email_address' => "string", 'first_name' => "string", 'last_name' => "string", 'activation_flag' => "bool"])]
    public function definition()
    {
        return [
            'email_address' => $this->faker->email,
            'first_name' => $this->faker->name,
            'last_name' => $this->faker->lastName,
            'activation_flag' => $this->faker->boolean,
        ];
    }
}
