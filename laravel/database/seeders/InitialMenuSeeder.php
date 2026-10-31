<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InitialMenuSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pastéis',
                'slug' => 'pasteis',
                'sort_order' => 1,
                'is_active' => true,
                'products' => [
                    [
                        'name' => 'Pastel Especial AKY',
                        'description' => 'Carne moída especial, queijo mussarela, presunto, ovo, milho e bacon crocante.',
                        'price' => 18.00,
                        'is_promotion' => true,
                        'promotional_price' => 15.00,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                    [
                        'name' => 'Pastel de Carne',
                        'description' => 'Carne bovina de primeira temperada com ervas finas e azeitonas.',
                        'price' => 12.00,
                        'is_promotion' => false,
                        'promotional_price' => null,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                    [
                        'name' => 'Pastel de Queijo',
                        'description' => 'Mussarela derretida em abundância com orégano fresco.',
                        'price' => 12.00,
                        'is_promotion' => false,
                        'promotional_price' => null,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                    [
                        'name' => 'Pastel Frango com Catupiry',
                        'description' => 'Frango desfiado suculento com o legítimo Catupiry.',
                        'price' => 14.00,
                        'is_promotion' => false,
                        'promotional_price' => null,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                ],
            ],
            [
                'name' => 'Salgados',
                'slug' => 'salgados',
                'sort_order' => 2,
                'is_active' => true,
                'products' => [
                    [
                        'name' => 'Coxinha com Catupiry',
                        'description' => 'Massa crocante por fora e macia por dentro com recheio cremoso.',
                        'price' => 8.50,
                        'is_promotion' => false,
                        'promotional_price' => null,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                    [
                        'name' => 'Enroladinho de Salsicha',
                        'description' => 'Salsicha especial envolta em massa frita crocante.',
                        'price' => 7.00,
                        'is_promotion' => false,
                        'promotional_price' => null,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                    [
                        'name' => 'Kibe com Queijo',
                        'description' => 'Kibe tradicional temperado com hortelã fresca e queijo mussarela.',
                        'price' => 8.50,
                        'is_promotion' => true,
                        'promotional_price' => 7.00,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                ],
            ],
            [
                'name' => 'Sucos & Bebidas',
                'slug' => 'sucos-bebidas',
                'sort_order' => 3,
                'is_active' => true,
                'products' => [
                    [
                        'name' => 'Suco de Laranja Natural 500ml',
                        'description' => 'Laranja fresca espremida na hora, 100% natural sem adição de açúcar.',
                        'price' => 9.00,
                        'is_promotion' => false,
                        'promotional_price' => null,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                    [
                        'name' => 'Suco de Maracujá 500ml',
                        'description' => 'Polpa fresca batida com água ou leite bem gelado.',
                        'price' => 9.50,
                        'is_promotion' => true,
                        'promotional_price' => 8.00,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                    [
                        'name' => 'Refrigerante Lata 350ml',
                        'description' => 'Coca-Cola, Guaraná Antarctica ou Sprite.',
                        'price' => 6.00,
                        'is_promotion' => false,
                        'promotional_price' => null,
                        'is_available' => true,
                        'photo_path' => null,
                    ],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $products = $catData['products'];
            unset($catData['products']);

            $category = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );

            foreach ($products as $prod) {
                Product::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $prod['name'],
                    ],
                    $prod
                );
            }
        }
    }
}

