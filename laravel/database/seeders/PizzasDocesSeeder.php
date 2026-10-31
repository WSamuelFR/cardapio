<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PizzasDocesSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'pizzas-doces'],
            [
                'name' => 'Pizzas Doces',
                'sort_order' => 6,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Pizza Doce de Abacaxi', 'desc' => 'Muçarela, abacaxi em pedaços, leite condensado e canela em pó.'],
            ['name' => 'Pizza Doce de Banana', 'desc' => 'Muçarela, banana fatiada, leite condensado e canela em pó.'],
            ['name' => 'Pizza Doce de Chocolate', 'desc' => 'Muçarela, chocolate cremoso e confetes coloridos.'],
            ['name' => 'Pizza Doce Prestígio', 'desc' => 'Muçarela, chocolate ao leite e flocos de coco ralado.'],
            ['name' => 'Pizza Doce Romeu e Julieta', 'desc' => 'Muçarela, goiabada cremosa e queijo coalho fatiado.'],
            ['name' => 'Pizza Doce Chocolate ao Leite', 'desc' => 'Muçarela e farta cobertura de chocolate ao leite.'],
            ['name' => 'Pizza Doce Chocolate Branco', 'desc' => 'Muçarela e farta cobertura de chocolate branco nobre.'],
            ['name' => 'Pizza Doce Dois Amores', 'desc' => 'Muçarela, mesclado especial de chocolate ao leite e chocolate branco.'],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                ],
                [
                    'description' => $item['desc'] . ' [Tamanhos: Pequena R$ 36,00 (6 fatias) | Média R$ 43,00 (8 fatias) | Grande R$ 52,00 (10 fatias)]',
                    'price' => 36.00,
                    'is_available' => true,
                    'is_combo' => false,
                    'is_promotion' => false,
                ]
            );
        }
    }
}

