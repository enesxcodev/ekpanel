<?php

namespace Database\Factories\Admin;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<Admin> */
class AdminFactory extends Factory
{
    protected static ?string $password;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'status' => true,
            'type' => AdminTypeEnum::Admin,
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => false]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => AdminTypeEnum::SuperAdmin]);
    }
}
