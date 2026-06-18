<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nom'        => fake()->lastName(),
            'prenom'     => fake()->firstName(),
            'sexe'       => fake()->randomElement(['Masculin', 'Féminin']),
            'telephone'  => '6' . fake()->numerify('########'),
            'email'      => fake()->unique()->safeEmail(),
            'password'   => static::$password ??= Hash::make('password'),
            'role'       => 'Client',
            'statut'     => 'Activé',
            'etat'       => 'Déconnecté',
            'points'     => 0,
            'void'       => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'Désactivé',
        ]);
    }
}