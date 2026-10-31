<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MiniCuscuzSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'mini-cuscuz'],
            [
                'name' => 'Mini Cuscuz',
                'sort_order' => 9,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Mini Cuscuz Carne de Sol', 'desc' => 'Cuscuz tradicional nordestino, carne de sol desfiada, muçarela derretida e vinagrete.'],
            ['name' => 'Mini Cuscuz Carne de Sol na Nata', 'desc' => 'Cuscuz tradicional, carne de sol desfiada na nata sertaneja, muçarela e vinagrete.'],
            ['name' => 'Mini Cuscuz Frango', 'desc' => 'Cuscuz tradicional, frango desfiado temperado, muçarela e vinagrete.'],
            ['name' => 'Mini Cuscuz Calabresa', 'desc' => 'Cuscuz tradicional, calabresa fatiada acebolada, muçarela e vinagrete.'],
            ['name' => 'Mini Cuscuz Bacon', 'desc' => 'Cuscuz tradicional, cubos de bacon crocante, muçarela e vinagrete.'],
            ['name' => 'Mini Cuscuz Queijo', 'desc' => 'Cuscuz tradicional, queijo coalho ou manteiga da terra, muçarela e vinagrete.'],
            ['name' => 'Mini Cuscuz Ovo', 'desc' => 'Cuscuz tradicional, ovo frito/mexido na manteiga, muçarela e vinagrete.'],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                ],
                [
                    'description' => $item['desc'],
                    'price' => 18.00,
                    'is_available' => true,
                    'is_combo' => false,
                    'is_promotion' => false,
                ]
            );
        }
    }
}

