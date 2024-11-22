<?php

namespace Database\Factories;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'is_in' => $this->faker->boolean,
            'point' => $this->faker->numberBetween(0, 100),
            'name' => $this->faker->name,
            'role' => $this->faker->randomElement(['admin', 'user']),
        ];
    }
}
