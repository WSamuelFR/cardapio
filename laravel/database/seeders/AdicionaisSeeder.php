<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AdicionaisSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'adicionais'],
            [
                'name' => 'Adicionais',
                'sort_order' => 13,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Adicional de Carne Tradicional', 'desc' => 'Hambúrguer de carne tradicional para sanduíches, tapiocas e cuscuz.', 'price' => 5.00],
            ['name' => 'Adicional de Carne Artesanal 100g', 'desc' => 'Blend bovino artesanal de 100g para sanduíches.', 'price' => 8.00],
            ['name' => 'Adicional de Carne Artesanal 150g', 'desc' => 'Blend bovino artesanal de 150g para sanduíches.', 'price' => 12.00],
            ['name' => 'Adicional de Bacon', 'desc' => 'Porção extra de bacon crocante.', 'price' => 5.00],
            ['name' => 'Adicional de Calabresa', 'desc' => 'Porção extra de calabresa fatiada.', 'price' => 5.00],
            ['name' => 'Adicional de Frango', 'desc' => 'Porção extra de frango desfiado temperado.', 'price' => 5.00],
            ['name' => 'Adicional de Carne de Sol', 'desc' => 'Porção extra de carne de sol desfiada da terra.', 'price' => 5.00],
            ['name' => 'Adicional de Presunto', 'desc' => 'Fatias adicionais de presunto.', 'price' => 4.00],
            ['name' => 'Adicional de Muçarela', 'desc' => 'Fartas fatias de queijo muçarela derretido.', 'price' => 5.00],
            ['name' => 'Adicional de Queijo Manteiga', 'desc' => 'Porção de queijo manteiga do sertão.', 'price' => 4.00],
            ['name' => 'Adicional de Queijo Coalho', 'desc' => 'Porção de queijo coalho assado na chapa.', 'price' => 5.00],
            ['name' => 'Adicional de Catupiry', 'desc' => 'Catupiry cremoso original.', 'price' => 4.00],
            ['name' => 'Adicional de Cheddar', 'desc' => 'Cheddar cremoso especial.', 'price' => 4.00],
            ['name' => 'Adicional de Ovo', 'desc' => 'Ovo frito ou mexido na manteiga.', 'price' => 3.00],
            ['name' => 'Adicional de Salada', 'desc' => 'Porção extra de alface fresca e tomate.', 'price' => 3.00],
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

