<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PasteloesSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'pasteloes'],
            [
                'name' => 'Pastelões',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        $items = [
            // Salgados
            ['name' => 'Pastelão Americano', 'description' => 'Presunto, catupiry e muçarela.', 'price' => 20.00],
            ['name' => 'Pastelão Atum', 'description' => 'Atum, catupiry e muçarela.', 'price' => 22.00],
            ['name' => 'Pastelão Bacon', 'description' => 'Bacon crocante e muçarela.', 'price' => 26.00],
            ['name' => 'Pastelão Bacon com Palmito', 'description' => 'Bacon, palmito e muçarela.', 'price' => 23.00],
            ['name' => 'Pastelão Baiano', 'description' => 'Calabresa, milho, pimentão, cebola e muçarela.', 'price' => 20.00],
            ['name' => 'Pastelão Calabresa', 'description' => 'Calabresa e muçarela.', 'price' => 20.00],
            ['name' => 'Pastelão Carne', 'description' => 'Carne moída temperada e muçarela.', 'price' => 20.00],
            ['name' => 'Pastelão Carne com Azeitona', 'description' => 'Carne moída, azeitona e muçarela.', 'price' => 21.00],
            ['name' => 'Pastelão Carne Seca', 'description' => 'Carne seca desfiada e muçarela.', 'price' => 21.00],
            ['name' => 'Pastelão Especial', 'description' => 'Frango, milho, ovo, parmesão, catupiry, cebola, tomate e muçarela.', 'price' => 23.00],
            ['name' => 'Pastelão Frango', 'description' => 'Frango desfiado e muçarela.', 'price' => 21.00],
            ['name' => 'Pastelão Frango com Catupiry', 'description' => 'Frango desfiado, catupiry e muçarela.', 'price' => 22.00],
            ['name' => 'Pastelão Frango com Milho', 'description' => 'Frango desfiado, milho e muçarela.', 'price' => 22.00],
            ['name' => 'Pastelão Frango com Bacon', 'description' => 'Frango desfiado, bacon e muçarela.', 'price' => 28.00],
            ['name' => 'Pastelão Frango com Cheddar', 'description' => 'Frango desfiado, cheddar e muçarela.', 'price' => 22.00],
            ['name' => 'Pastelão Frango Especial', 'description' => 'Frango desfiado, cheddar, bacon e muçarela.', 'price' => 28.00],
            ['name' => 'Pastelão Frango com Calabresa', 'description' => 'Frango desfiado, calabresa e muçarela.', 'price' => 22.00],
            ['name' => 'Pastelão Queijo', 'description' => 'Queijo manteiga, coalho ou muçarela.', 'price' => 19.00],
            ['name' => 'Pastelão Misto', 'description' => 'Presunto e muçarela.', 'price' => 20.00],
            ['name' => 'Pastelão Português', 'description' => 'Presunto, milho, ovo, tomate, cebola, pimentão e muçarela.', 'price' => 20.00],
            ['name' => 'Pastelão Pizza', 'description' => 'Presunto, muçarela, molho de tomate e orégano.', 'price' => 22.00],
            ['name' => 'Pastelão Turbinado', 'description' => 'Calabresa, frango, bacon e muçarela.', 'price' => 27.00],
            ['name' => 'Pastelão 4 Queijos', 'description' => 'Cheddar, catupiry, parmesão e muçarela.', 'price' => 22.00],

            // Doces
            ['name' => 'Pastelão Doce Banana com Chocolate', 'description' => 'Banana fatiada com chocolate cremoso.', 'price' => 20.00],
            ['name' => 'Pastelão Doce Banana com Doce de Leite', 'description' => 'Banana fatiada com doce de leite cremoso.', 'price' => 20.00],
            ['name' => 'Pastelão Doce Chocolate ao Leite', 'description' => 'Recheio generoso de chocolate ao leite.', 'price' => 20.00],
            ['name' => 'Pastelão Doce Chocolate Branco', 'description' => 'Recheio generoso de chocolate branco.', 'price' => 20.00],
            ['name' => 'Pastelão Doce Romeu e Julieta', 'description' => 'Queijo com goiabada cremosa.', 'price' => 20.00],

            // Monte seu Pastel
            ['name' => 'Monte Seu Pastel (Até 4 Ingredientes)', 'description' => 'Escolha até 4 ingredientes: bacon, calabresa, carne moída, carne seca, frango, catupiry, cheddar, queijo coalho, queijo manteiga, muçarela, parmesão, presunto, azeitona, dueto, milho, ovo, tomate, cebola, banana, canela, chocolate ao leite, chocolate branco, doce de leite, goiabada ou leite condensado.', 'price' => 15.00],
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

