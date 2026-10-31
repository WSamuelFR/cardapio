<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SanduichesClassicosSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'sanduiches-classicos'],
            [
                'name' => 'Sanduíches Clássicos',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $items = [
            [
                'name' => 'Americano',
                'description' => 'Pão bola, ovo, queijo, presunto, molho especial e salada fresca.',
                'price' => 15.00,
            ],
            [
                'name' => 'Bauru',
                'description' => 'Pão de forma, queijo, presunto, molho especial e salada.',
                'price' => 9.00,
            ],
            [
                'name' => 'Cheddar Bacon',
                'description' => 'Pão bola, carne, bacon crocante, cheddar, molho especial e cebola ao shoyu.',
                'price' => 16.00,
            ],
            [
                'name' => 'Duplo Cheddar Bacon',
                'description' => 'Pão bola, dobro de carne, dobro de bacon, dobro de cheddar, molho especial e cebola ao shoyu.',
                'price' => 21.00,
            ],
            [
                'name' => 'Especial',
                'description' => 'Pão bola, carne, queijo, bacon, presunto, ovo, salsicha, molho especial e salada.',
                'price' => 21.00,
            ],
            [
                'name' => 'Hambúrguer',
                'description' => 'Pão bola, carne bovina, molho especial e salada.',
                'price' => 11.00,
            ],
            [
                'name' => 'Misto Quente',
                'description' => 'Pão de forma tostado com queijo e presunto.',
                'price' => 7.50,
            ],
            [
                'name' => 'Sanduíche de Queijo',
                'description' => 'Pão bola ou de forma com queijo manteiga, coalho ou muçarela.',
                'price' => 7.00,
            ],
            [
                'name' => 'Super Tudo AKY Lanches',
                'description' => 'Pão bola, dobro de carne, queijo, bacon, calabresa, ovo, presunto, salsicha, cheddar, milho, ervilha, molho especial e salada.',
                'price' => 26.00,
            ],
            [
                'name' => 'X-Bacon',
                'description' => 'Pão bola, bacon crocante, queijo, molho especial e salada.',
                'price' => 15.00,
            ],
            [
                'name' => 'X-Burguer',
                'description' => 'Pão bola, carne, queijo, molho especial e salada.',
                'price' => 13.00,
            ],
            [
                'name' => 'X-Burguer Bacon',
                'description' => 'Pão bola, carne, bacon, queijo, molho especial e salada.',
                'price' => 17.00,
            ],
            [
                'name' => 'X-Burguer Calabresa',
                'description' => 'Pão bola, carne, calabresa, queijo, molho especial e salada.',
                'price' => 17.00,
            ],
            [
                'name' => 'X-Burguer Egg',
                'description' => 'Pão bola, carne, queijo, ovo, molho especial e salada.',
                'price' => 16.00,
            ],
            [
                'name' => 'X-Calabresa',
                'description' => 'Pão bola, calabresa fatiada, queijo, molho especial e salada.',
                'price' => 15.00,
            ],
            [
                'name' => 'X-Frango Burguer',
                'description' => 'Pão bola, carne bovina, frango desfiado, queijo, molho especial e salada.',
                'price' => 17.00,
            ],
            [
                'name' => 'X-Frango Bacon',
                'description' => 'Pão bola, frango desfiado, bacon, queijo, molho especial e salada.',
                'price' => 19.00,
            ],
            [
                'name' => 'X-Frango',
                'description' => 'Pão bola, frango desfiado, queijo, molho especial e salada.',
                'price' => 15.00,
            ],
            [
                'name' => 'X-Salada',
                'description' => 'Pão bola, presunto, carne, queijo, molho especial e salada fresca.',
                'price' => 16.00,
            ],
            [
                'name' => 'O Quarteirão',
                'description' => 'Pão bola, 4 carnes suculentas, 4 fatias de queijo cheddar e molho especial da casa.',
                'price' => 24.00,
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

