<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * Define o estado padrão do modelo.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),           // Faker sempre assim
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Mudar@123'),
            'remember_token' => Str::random(10),
            'access_level' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Email não verificado.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Usuário admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => 1,
        ]);
    }

    /**
     * Usuário inativo.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}