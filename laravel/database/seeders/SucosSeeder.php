<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SucosSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'sucos'],
            [
                'name' => 'Sucos',
                'sort_order' => 12,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Suco Natural de Abacaxi', 'desc' => 'Suco natural feito na hora (Polpa ou fruta).', 'price' => 10.00],
            ['name' => 'Suco Natural de Abacaxi com Hortelã', 'desc' => 'Suco refrescante de abacaxi com folhas frescas de hortelã.', 'price' => 10.00],
            ['name' => 'Suco Natural de Acerola', 'desc' => 'Suco rico em vitamina C feito na hora.', 'price' => 10.00],
            ['name' => 'Suco Natural de Açaí', 'desc' => 'Suco cremoso e energético de açaí puro.', 'price' => 10.00],
            ['name' => 'Suco Natural de Ameixa', 'desc' => 'Suco natural de ameixa selecionada.', 'price' => 10.00],
            ['name' => 'Suco Natural de Banana', 'desc' => 'Vitamina/suco cremoso de banana.', 'price' => 10.00],
            ['name' => 'Suco Natural de Cajá', 'desc' => 'Sabor típico do Nordeste, refrescante e cítrico.', 'price' => 10.00],
            ['name' => 'Suco Natural de Caju', 'desc' => 'Suco natural de caju selecionado.', 'price' => 10.00],
            ['name' => 'Suco Natural de Cupuaçu', 'desc' => 'Sabor marcante e aroma tropical do cupuaçu.', 'price' => 10.00],
            ['name' => 'Suco Natural de Goiaba', 'desc' => 'Suco cremoso de goiaba fresca.', 'price' => 10.00],
            ['name' => 'Suco Natural de Graviola', 'desc' => 'Suco suave e aveludado de graviola.', 'price' => 10.00],
            ['name' => 'Suco Guarabom com Açaí', 'desc' => 'Misto energético de guaraná com açaí.', 'price' => 10.00],
            ['name' => 'Suco Guarabom com Morango', 'desc' => 'Misto refrescante de guaraná com morango.', 'price' => 10.00],
            ['name' => 'Suco Guarabom com Mel', 'desc' => 'Misto revigorante de guaraná com mel puro.', 'price' => 10.00],
            ['name' => 'Suco Natural de Laranja', 'desc' => 'Suco da fruta espremido na hora.', 'price' => 10.00],
            ['name' => 'Limonada Suíça', 'desc' => 'Limonada fresca e refrescante batida com gelo.', 'price' => 10.00],
            ['name' => 'Suco Natural de Manga', 'desc' => 'Suco encorpado e doce de manga fresca.', 'price' => 10.00],
            ['name' => 'Suco Natural de Mangaba', 'desc' => 'Sabor nativo e aromático de mangaba sertaneja.', 'price' => 10.00],
            ['name' => 'Suco Natural de Maracujá', 'desc' => 'Suco calmante e refrescante de maracujá puro.', 'price' => 10.00],
            ['name' => 'Suco Natural de Morango', 'desc' => 'Suco feito com morangos frescos selecionados.', 'price' => 10.00],
            ['name' => 'Suco Natural de Uva', 'desc' => 'Suco natural integral de uva.', 'price' => 10.00],
            ['name' => 'Suco Misto (Duas Frutas)', 'desc' => 'Combinação especial de até dois sabores de frutas da sua escolha.', 'price' => 12.00],
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

