<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Bezhanov\Faker\Provider\Commerce;

class ProductFactory extends Factory
{
    

    public function definition(): array
    {
        return [
            // Usando sentence (uma frase curta) ou words (palavras soltas)
            'description'    => ucfirst($this->faker->catchPhrase()), 
            'brand'          => $this->faker->company(),
            'model'          => $this->faker->bothify('??-###'), // Ex: AB-123
            'size'           => $this->faker->randomElement(['P', 'M', 'G', 'GG', '42', '44']),
            'collection'     => 'Coleção ' . $this->faker->word(),
            'gender'         => $this->faker->randomElement(['Masculino', 'Feminino', 'Unissex']),
            'cost_price'     => $this->faker->randomFloat(2, 50, 150),
            'sale_price'     => $this->faker->randomFloat(2, 200, 500),
            'barcode'        => $this->faker->ean13(),
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'is_active'      => true,
            'is_featured'    => $this->faker->boolean(20), // 20% de chance de ser destaque
        ];
    }
}