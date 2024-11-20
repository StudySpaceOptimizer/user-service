<?php

namespace Database\Factories;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'email' => $this->faker->unique()->safeEmail,
            'is_in' => $this->faker->boolean,
            'point' => $this->faker->numberBetween(0, 8),
            'name' => $this->faker->name,
            'phone' => $this->faker->unique()->phoneNumber,
            'id_card' => $this->faker->unique()->randomNumber(8, true),
        ];
    }
}
