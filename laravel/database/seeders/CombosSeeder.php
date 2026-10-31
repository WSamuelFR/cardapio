<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class CombosSeeder extends Seeder
{
    public function run(): void
    {
        $combos = [
            [
                'name' => 'Combo 1: Pastel Especial + Coca-Cola',
                'description' => '1 Pastel Especial AKY completo + 1 Refrigerante Coca-Cola Lata 350ml gelada.',
                'price' => 24.00,
                'is_promotion' => true,
                'promotional_price' => 19.90,
                'is_combo' => true,
                'is_available' => true,
            ],
            [
                'name' => 'Combo Casal: 2 Pastéis + 2 Sucos Naturais',
                'description' => '2 Pastéis à sua escolha (Carne, Queijo ou Frango) + 2 Sucos Naturais 500ml.',
                'price' => 42.00,
                'is_promotion' => true,
                'promotional_price' => 34.90,
                'is_combo' => true,
                'is_available' => true,
            ],
            [
                'name' => 'Combo Família: 4 Pastéis + Guaraná 2L',
                'description' => '4 Pastéis grandes recheados + 1 Refrigerante Guaraná Antarctica 2 Litros.',
                'price' => 65.00,
                'is_promotion' => true,
                'promotional_price' => 52.00,
                'is_combo' => true,
                'is_available' => true,
            ],
            [
                'name' => 'Combo Lanche Rápido: Coxinha + Suco de Laranja',
                'description' => '1 Coxinha com Catupiry crocante por fora + 1 Suco de Laranja 400ml feito na hora.',
                'price' => 18.00,
                'is_promotion' => true,
                'promotional_price' => 14.50,
                'is_combo' => true,
                'is_available' => true,
            ],
        ];

        foreach ($combos as $comboData) {
            Product::firstOrCreate(
                ['name' => $comboData['name']],
                $comboData
            );
        }
    }
}

