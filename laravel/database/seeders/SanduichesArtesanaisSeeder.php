<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SanduichesArtesanaisSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'sanduiches-artesanais'],
            [
                'name' => 'Sanduíches Artesanais',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $items = [
            [
                'name' => 'Supremo Burguer',
                'description' => 'Pão brioche, blend bovino artesanal de 150g, queijo cheddar, cebola caramelizada, molho e salada.',
                'price' => 20.00,
            ],
            [
                'name' => 'Supremo Bacon',
                'description' => 'Pão brioche, blend bovino de 150g, muçarela, bacon crocante, cebola caramelizada, molho e salada.',
                'price' => 23.00,
            ],
            [
                'name' => 'Supremo Calabresa',
                'description' => 'Pão brioche, blend bovino de 150g, muçarela, calabresa, cebola caramelizada, molho e salada.',
                'price' => 23.00,
            ],
            [
                'name' => 'Supremo Cheddar Bacon',
                'description' => 'Pão brioche, blend bovino de 150g, queijo cheddar, bacon crocante, cebola caramelizada, molho e salada.',
                'price' => 23.00,
            ],
            [
                'name' => 'Supremo Egg',
                'description' => 'Pão brioche, blend bovino de 150g, muçarela, ovo, cebola caramelizada, molho e salada.',
                'price' => 23.00,
            ],
            [
                'name' => 'Supremo Abacaxi',
                'description' => 'Pão brioche, blend bovino de 150g, muçarela, bacon, abacaxi grelhado, cebola caramelizada, molho e salada.',
                'price' => 28.00,
            ],
            [
                'name' => 'X-Filé de Frango',
                'description' => 'Pão brioche, filé de frango grelhado, queijo coalho, molho especial e salada.',
                'price' => 17.00,
            ],
            [
                'name' => 'X-Filé-Mignon',
                'description' => 'Pão brioche, filé-mignon em tiras macias, queijo derretido, molho especial e salada.',
                'price' => 20.00,
            ],
            [
                'name' => 'Big Mek AKY',
                'description' => 'Pão brioche, 2 blends bovinos de 100g cada, queijo, picles crocante, cebola caramelizada, molho especial e salada.',
                'price' => 25.00,
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

