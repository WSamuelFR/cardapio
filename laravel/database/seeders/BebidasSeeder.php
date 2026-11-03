<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BebidasSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'bebidas'],
            [
                'name' => 'Bebidas',
                'sort_order' => 11,
                'is_active' => true,
            ]
        );

        $items = [
            // Águas e H2OH!
            ['name' => 'Água Mineral sem Gás 500ml', 'desc' => 'Garrafa de água mineral natural 500ml bem gelada.', 'price' => 3.00],
            ['name' => 'Água Mineral com Gás 500ml', 'desc' => 'Garrafa de água mineral gaseificada 500ml bem gelada.', 'price' => 4.00],
            ['name' => 'Água Tônica Lata 350ml', 'desc' => 'Lata de água tônica refrescante 350ml.', 'price' => 6.00],
            ['name' => 'H2OH! Limão 500ml', 'desc' => 'Garrafa H2OH! sabor Limão levemente gaseificada 500ml.', 'price' => 8.00],
            ['name' => 'H2OH! Limoneto (Citrus) 500ml', 'desc' => 'Garrafa H2OH! sabor Limoneto / Citrus 500ml bem gelada.', 'price' => 8.00],

            // Pitxulinha
            ['name' => 'Coca-Cola Pitxulinha', 'desc' => 'Garrafinha caçulinha infantil 200ml bem gelada.', 'price' => 5.00],
            ['name' => 'Guaraná Antarctica Pitxulinha', 'desc' => 'Garrafinha caçulinha Guaraná 200ml geladinha.', 'price' => 5.00],
            ['name' => 'Fanta Laranja Pitxulinha', 'desc' => 'Garrafinha caçulinha Fanta Laranja 200ml infantil.', 'price' => 5.00],

            // Garrafas Individuais (NS / KS)
            ['name' => 'Coca-Cola NS', 'desc' => 'Garrafa de Coca-Cola Não Retornável.', 'price' => 5.00],
            ['name' => 'Coca-Cola KS 290ml', 'desc' => 'Garrafa de vidro tradicional 290ml servida geladíssima.', 'price' => 6.00],

            // Refrigerantes Lata 350ml
            ['name' => 'Coca-Cola Lata 350ml', 'desc' => 'Lata de Coca-Cola Tradicional 350ml bem gelada.', 'price' => 7.00],
            ['name' => 'Coca-Cola Zero Lata 350ml', 'desc' => 'Lata de Coca-Cola Sem Açúcar 350ml bem gelada.', 'price' => 7.00],
            ['name' => 'Guaraná Antarctica Lata 350ml', 'desc' => 'Lata de Guaraná Antarctica Tradicional 350ml.', 'price' => 7.00],
            ['name' => 'Fanta Laranja Lata 350ml', 'desc' => 'Lata de Fanta Laranja 350ml bem gelada.', 'price' => 7.00],
            ['name' => 'Fanta Uva Lata 350ml', 'desc' => 'Lata de Fanta Uva 350ml bem gelada.', 'price' => 7.00],
            ['name' => 'Sprite Lata 350ml', 'desc' => 'Lata de refrigerante Sprite limão refrescante 350ml.', 'price' => 7.00],

            // Refrigerantes 500/600ml
            ['name' => 'Coca-Cola 600ml', 'desc' => 'Garrafa de Coca-Cola Tradicional 600ml bem gelada.', 'price' => 8.00],
            ['name' => 'Coca-Cola Zero 600ml', 'desc' => 'Garrafa de Coca-Cola Sem Açúcar 600ml gelada.', 'price' => 8.00],
            ['name' => 'Guaraná Antarctica 600ml', 'desc' => 'Garrafa de Guaraná Antarctica 600ml bem gelada.', 'price' => 8.00],
            ['name' => 'Fanta Laranja 600ml', 'desc' => 'Garrafa de Fanta Laranja 600ml bem gelada.', 'price' => 8.00],
            ['name' => 'Sprite 600ml', 'desc' => 'Garrafa de refrigerante Sprite 600ml bem gelada.', 'price' => 8.00],

            // Refrigerantes 1 Litro
            ['name' => 'Coca-Cola 1 Litro', 'desc' => 'Garrafa de refrigerante Coca-Cola 1 Litro para compartilhar.', 'price' => 12.00],
            ['name' => 'Guaraná Antarctica 1 Litro', 'desc' => 'Garrafa de Guaraná Antarctica 1 Litro gelada.', 'price' => 12.00],
            ['name' => 'Fanta Laranja 1 Litro', 'desc' => 'Garrafa de Fanta Laranja 1 Litro bem gelada.', 'price' => 12.00],
            ['name' => 'Cajuína São Geraldo 1 Litro', 'desc' => 'Refrigerante regional de caju São Geraldo 1 Litro.', 'price' => 12.00],

            // Refrigerantes 2 Litros
            ['name' => 'Coca-Cola 2 Litros', 'desc' => 'Garrafa de Coca-Cola Tradicional 2 Litros para toda a família.', 'price' => 20.00],
            ['name' => 'Coca-Cola Zero 2 Litros', 'desc' => 'Garrafa de Coca-Cola Sem Açúcar 2 Litros.', 'price' => 20.00],
            ['name' => 'Guaraná Antarctica 2 Litros', 'desc' => 'Garrafa de Guaraná Antarctica 2 Litros bem gelada.', 'price' => 20.00],
            ['name' => 'Fanta Laranja 2 Litros', 'desc' => 'Garrafa de refrigerante Fanta Laranja 2 Litros.', 'price' => 20.00],
            ['name' => 'Fanta Uva 2 Litros', 'desc' => 'Garrafa de refrigerante Fanta Uva 2 Litros.', 'price' => 20.00],
            ['name' => 'Sprite 2 Litros', 'desc' => 'Garrafa de refrigerante Sprite limão 2 Litros.', 'price' => 20.00],
            ['name' => 'Cajuína São Geraldo 2 Litros', 'desc' => 'Refrigerante regional de caju São Geraldo 2 Litros.', 'price' => 20.00],
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
