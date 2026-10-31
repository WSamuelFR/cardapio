<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PizzasTradicionaisSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'pizzas-tradicionais'],
            [
                'name' => 'Pizzas Tradicionais',
                'sort_order' => 5,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Pizza À Moda da Casa', 'desc' => 'Molho de tomate, muçarela, frango desfiado, milho, ovo, tomate e orégano.'],
            ['name' => 'Pizza Amorzinho', 'desc' => 'Molho de tomate, muçarela, peito de peru, frango, catupiry, cebola, tomate e orégano.'],
            ['name' => 'Pizza Atum', 'desc' => 'Molho de tomate, muçarela, atum sólido, cebola, tomate e orégano.'],
            ['name' => 'Pizza Baconpiry', 'desc' => 'Molho de tomate, muçarela, bacon em fatias, catupiry, cebola, tomate e orégano.'],
            ['name' => 'Pizza Bauru', 'desc' => 'Molho de tomate, muçarela, presunto fatiado, tomate e orégano.'],
            ['name' => 'Pizza Cachorro Quente', 'desc' => 'Molho de tomate, muçarela, carne moída ao molho, salsicha, parmesão, batata palha, cebola, tomate e orégano.'],
            ['name' => 'Pizza Calabrapiry', 'desc' => 'Molho de tomate, muçarela, calabresa fatiada, catupiry, cebola, tomate e orégano.'],
            ['name' => 'Pizza Calabresa', 'desc' => 'Molho de tomate, muçarela, calabresa fatiada, cebola em rodelas, tomate e orégano.'],
            ['name' => 'Pizza Calabresa Mineira', 'desc' => 'Molho de tomate, muçarela, calabresa, bacon crocante, milho verde, tomate e orégano.'],
            ['name' => 'Pizza Crocante', 'desc' => 'Molho de tomate, muçarela, presunto, bacon, milho verde, batata palha, tomate e orégano.'],
            ['name' => 'Pizza Frango', 'desc' => 'Molho de tomate, muçarela, frango desfiado temperado, tomate e orégano.'],
            ['name' => 'Pizza Frango com Catupiry', 'desc' => 'Molho de tomate, muçarela, frango desfiado, catupiry cremoso, tomate e orégano.'],
            ['name' => 'Pizza Frango Xadrez', 'desc' => 'Molho de tomate, muçarela, frango desfiado, cheddar, catupiry, tomate e orégano.'],
            ['name' => 'Pizza Frango com Bacon', 'desc' => 'Molho de tomate, muçarela, frango desfiado, bacon crocante, catupiry, cebola, tomate e orégano.'],
            ['name' => 'Pizza Frango com Cheddar', 'desc' => 'Molho de tomate, muçarela, frango desfiado, cheddar cremoso, tomate e orégano.'],
            ['name' => 'Pizza Havaiana', 'desc' => 'Molho de tomate, muçarela, lombo canadense, abacaxi em cubos e orégano.'],
            ['name' => 'Pizza Lombo com Catupiry', 'desc' => 'Molho de tomate, muçarela, lombo canadense, catupiry, cebola, tomate e orégano.'],
            ['name' => 'Pizza 4 Queijos', 'desc' => 'Molho de tomate, muçarela, cheddar, catupiry, parmesão ralado, tomate e orégano.'],
            ['name' => 'Pizza Mafiosa', 'desc' => 'Molho de tomate, muçarela, calabresa, milho, bacon, catupiry, cheddar, cebola, tomate e orégano.'],
            ['name' => 'Pizza Muçarela', 'desc' => 'Molho de tomate, farta camada de muçarela, rodelas de tomate e orégano.'],
            ['name' => 'Pizza Palmito', 'desc' => 'Molho de tomate, muçarela, palmito em rodelas, tomate e orégano.'],
            ['name' => 'Pizza Portuguesa', 'desc' => 'Molho de tomate, muçarela, presunto, ovo cozido, milho, pimentão, cebola, tomate e orégano.'],
            ['name' => 'Pizza Sertaneja', 'desc' => 'Molho de tomate, muçarela, carne de sol desfiada, queijo coalho fatiado, cebola, tomate e orégano.'],
            ['name' => 'Pizza Teixeirense', 'desc' => 'Molho de tomate, muçarela, carne de sol da terra, cebola refogada, tomate e orégano.'],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                ],
                [
                    'description' => $item['desc'] . ' [Tamanhos: Pequena R$ 36,00 (6 fatias) | Média R$ 43,00 (8 fatias) | Grande R$ 52,00 (10 fatias)]',
                    'price' => 36.00, // Preço base Pequena
                    'is_available' => true,
                    'is_combo' => false,
                    'is_promotion' => false,
                ]
            );
        }
    }
}

