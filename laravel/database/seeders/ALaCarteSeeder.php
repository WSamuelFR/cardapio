<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ALaCarteSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'a-la-carte'],
            [
                'name' => 'À La Carte',
                'sort_order' => 8,
                'is_active' => true,
            ]
        );

        $items = [
            [
                'name' => 'Carne de Sol Completa',
                'description' => 'Acompanha arroz, feijão, macarrão, cuscuz, purê de batata, batatinha frita, farofa, salada e vinagrete.',
                'price' => 150.00,
            ],
            [
                'name' => 'Carne de Sol na Nata',
                'description' => 'Acompanha arroz, feijão, macarrão, cuscuz, purê de batata, batatinha frita, farofa, salada e vinagrete.',
                'price' => 150.00,
            ],
            [
                'name' => 'Filé à Parmegiana',
                'description' => 'Acompanha arroz branco, talharim e purê de batata artesanal.',
                'price' => 120.00,
            ],
            [
                'name' => 'Macarronada à Bolonhesa (Meia Porção)',
                'description' => 'Massa tradicional com molho bolonhesa artesanal de carne e queijo ralado.',
                'price' => 60.00,
            ],
            [
                'name' => 'Macarronada à Bolonhesa (Porção Inteira)',
                'description' => 'Massa tradicional com molho bolonhesa artesanal de carne e queijo ralado para família.',
                'price' => 100.00,
            ],
            [
                'name' => 'Macarronada de Frango (Meia Porção)',
                'description' => 'Massa tradicional com molho de frango desfiado cremoso e queijo.',
                'price' => 60.00,
            ],
            [
                'name' => 'Macarronada de Frango (Porção Inteira)',
                'description' => 'Massa tradicional com molho de frango desfiado cremoso e queijo para família.',
                'price' => 100.00,
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
                    'is_combo' => false,
                    'is_promotion' => false,
                ]
            );
        }
    }
}

