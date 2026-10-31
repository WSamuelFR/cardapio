<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PetiscosSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'petiscos'],
            [
                'name' => 'Petiscos',
                'sort_order' => 7,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Batatinha Frita', 'desc' => 'Porção de batatas fritas crocantes e sequinhas.', 'price' => 25.00],
            ['name' => 'Batatinha Frita com Calabresa', 'desc' => 'Batata frita crocante acompanhada de calabresa fatiada acebolada.', 'price' => 32.00],
            ['name' => 'Batatinha Frita Especial', 'desc' => 'Batata frita coberta com fatias crocantes de bacon e generoso cheddar cremoso.', 'price' => 35.00],
            ['name' => 'Calabresa Acebolada', 'desc' => 'Porção de calabresa fatiada frita com cebolas douradas.', 'price' => 25.00],
            ['name' => 'Carne de Sol com Macaxeira', 'desc' => 'Carne de sol da terra refogada com macaxeira cozida e frita na manteiga.', 'price' => 52.00],
            ['name' => 'Carne de Sol na Nata', 'desc' => 'Carne de sol desfiada cozida no molho de nata sertaneja.', 'price' => 52.00],
            ['name' => 'Costelinha de Porco', 'desc' => 'Porção de costelinha suína frita crocante e temperada.', 'price' => 35.00],
            ['name' => 'Filé Bovino com Batatinha Frita', 'desc' => 'Tiras de filé bovino acebolado acompanhado de batatas fritas.', 'price' => 52.00],
            ['name' => 'Frango à Passarinho com Batatinha Frita', 'desc' => 'Frango em pedaços crocante frito à passarinho com batata frita.', 'price' => 48.00],
            ['name' => 'Peixe Frito', 'desc' => 'Postas de peixe temperadas e fritas douradas.', 'price' => 70.00],
            ['name' => 'Mistão', 'desc' => 'Tiras de carne bovina, calabresa acebolada e batatinha frita.', 'price' => 55.00],
            ['name' => 'Queijo Coalho Assado', 'desc' => 'Porção de queijo coalho assado na chapa.', 'price' => 22.00],
            ['name' => 'Queijo Coalho com Azeitona', 'desc' => 'Queijo coalho em cubos assado acompanhado de azeitonas verdes.', 'price' => 29.00],
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

