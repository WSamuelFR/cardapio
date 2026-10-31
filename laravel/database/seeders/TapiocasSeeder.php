<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class TapiocasSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'tapiocas'],
            [
                'name' => 'Tapiocas',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Tapioca de Bacon', 'desc' => 'Goma de tapioca hidratada recheada com bacon crocante.', 'price' => 12.00],
            ['name' => 'Tapioca de Bacon com Queijo', 'desc' => 'Goma de tapioca recheada com bacon e queijo derretido.', 'price' => 15.00],
            ['name' => 'Tapioca de Calabresa', 'desc' => 'Goma de tapioca recheada com calabresa fatiada acebolada.', 'price' => 12.00],
            ['name' => 'Tapioca de Calabresa com Queijo', 'desc' => 'Goma de tapioca recheada com calabresa e queijo derretido.', 'price' => 15.00],
            ['name' => 'Tapioca de Carne Moída', 'desc' => 'Goma de tapioca recheada com carne moída temperada.', 'price' => 12.00],
            ['name' => 'Tapioca de Carne Moída com Queijo', 'desc' => 'Goma de tapioca recheada com carne moída e queijo.', 'price' => 15.00],
            ['name' => 'Tapioca de Carne Seca', 'desc' => 'Goma de tapioca recheada com carne seca desfiada.', 'price' => 12.00],
            ['name' => 'Tapioca de Carne Seca com Queijo', 'desc' => 'Goma de tapioca recheada com carne seca e queijo.', 'price' => 15.00],
            ['name' => 'Tapioca de Frango', 'desc' => 'Goma de tapioca recheada com frango desfiado suculento.', 'price' => 12.00],
            ['name' => 'Tapioca de Frango com Queijo', 'desc' => 'Goma de tapioca recheada com frango desfiado e queijo.', 'price' => 15.00],
            ['name' => 'Tapioca de Queijo com Presunto', 'desc' => 'Goma de tapioca recheada com queijo e presunto fatiado.', 'price' => 10.00],
            ['name' => 'Tapioca de Ovo', 'desc' => 'Goma de tapioca recheada com ovo mexido na manteiga.', 'price' => 8.00],
            ['name' => 'Tapioca de Queijo', 'desc' => 'Goma de tapioca recheada com farta camada de queijo derretido.', 'price' => 8.00],
            ['name' => 'Tapioca Simples com Manteiga', 'desc' => 'Goma de tapioca tradicional passada na manteiga da terra.', 'price' => 6.00],
            ['name' => 'Adicional de Salada para Tapioca', 'desc' => 'Porção de salada fresca (alface e tomate) para acompanhar sua tapioca.', 'price' => 3.00],
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

