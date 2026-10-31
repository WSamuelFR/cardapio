<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CombosEspeciaisSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'combos-especiais'],
            [
                'name' => 'Combos Especiais',
                'sort_order' => 4,
                'is_active' => true,
            ]
        );

        $items = [
            [
                'name' => 'Combo do Dia',
                'description' => 'Batata frita crocante + Sanduíche do dia + Refrigerante pitxulinha.',
                'price' => 23.99,
            ],
            [
                'name' => 'Combo Kids',
                'description' => 'Batata frita P + X-Burguer + Refrigerante pitxulinha.',
                'price' => 25.99,
            ],
            [
                'name' => 'Combo do Seu Jeito',
                'description' => 'Batata frita P + Sanduíche à sua escolha + Refrigerante pitxulinha ou Milkshake (Opções de sanduíches: x-bacon, americano, x-frango, x-burguer egg, x-burguer, x-calabresa, x-salada, burguer egg, cheddar bacon, x-egg, hambúrguer ou bauru).',
                'price' => 28.99,
            ],
            [
                'name' => 'Combo Nuggets',
                'description' => 'Porção de nuggets crocantes + Sanduíche + Refrigerante pitxulinha ou Milkshake.',
                'price' => 32.99,
            ],
            [
                'name' => 'Combo Especial',
                'description' => 'Batata frita P + Sanduíche especial + Refrigerante pitxulinha ou Milkshake (Opções: especial, duplo cheddar bacon, x-burguer bacon, x-burguer calabresa, x-franburguer ou x-franbacon).',
                'price' => 31.99,
            ],
            [
                'name' => 'Combo Família',
                'description' => '3 X-Burguer Egg + Batata Frita Grande + Coca-Cola de 1 Litro.',
                'price' => 53.99,
            ],
            [
                'name' => 'Combo Pastelão',
                'description' => 'Pastelão à sua escolha + Batata frita P + Refrigerante pitxulinha ou Milkshake.',
                'price' => 36.99,
            ],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                ],
                [
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'is_available' => true,
                    'is_combo' => true,
                    'is_promotion' => true,
                    'promotional_price' => $item['price'],
                ]
            );
        }
    }
}

