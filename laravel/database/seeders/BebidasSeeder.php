<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BebidasSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'bebidas'],
            [
                'name' => 'Bebidas',
                'sort_order' => 11,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Água Mineral sem Gás', 'desc' => 'Garrafa de água mineral 500ml bem gelada.', 'price' => 3.00],
            ['name' => 'Água Mineral com Gás', 'desc' => 'Garrafa de água mineral gaseificada 500ml bem gelada.', 'price' => 4.00],
            ['name' => 'Água Tônica', 'desc' => 'Lata de água tônica refrescante 350ml.', 'price' => 6.00],
            ['name' => 'H2OH!', 'desc' => 'Garrafa H2OH! Citrus / Limão levemente gaseificada 500ml.', 'price' => 8.00],
            ['name' => 'Refrigerante Pitxulinha', 'desc' => 'Garrafinha caçulinha / pitxula infantil.', 'price' => 5.00],
            ['name' => 'Refrigerante Lata', 'desc' => 'Refrigerante em lata 350ml (Coca-Cola, Guaraná Antarctica, Fanta Laranja).', 'price' => 7.00],
            ['name' => 'Coca-Cola NS', 'desc' => 'Garrafa de Coca-Cola Não Retornável.', 'price' => 5.00],
            ['name' => 'Coca-Cola KS', 'desc' => 'Garrafa de vidro tradicional 290ml servida geladíssima.', 'price' => 6.00],
            ['name' => 'Refrigerante 500/600 ml', 'desc' => 'Garrafa de refrigerante 500ml a 600ml gelada.', 'price' => 8.00],
            ['name' => 'Refrigerante 1 Litro', 'desc' => 'Garrafa de refrigerante 1 Litro para compartilhar.', 'price' => 12.00],
            ['name' => 'Refrigerante 2 Litros', 'desc' => 'Garrafa de refrigerante 2 Litros para toda a família.', 'price' => 20.00],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                ],
                [
                    'description' => $item['desc'],
                    'price' => $item['price'],
                    'is_available' => true,
                    'is_combo' => false,
                    'is_promotion' => false,
                ]
            );
        }
    }
}

