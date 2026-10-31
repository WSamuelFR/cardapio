<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SanduichesClassicosSeeder::class,
            SanduichesArtesanaisSeeder::class,
            PasteloesSeeder::class,
            CombosEspeciaisSeeder::class,
            PizzasTradicionaisSeeder::class,
            PizzasDocesSeeder::class,
            PetiscosSeeder::class,
            ALaCarteSeeder::class,
            MiniCuscuzSeeder::class,
            TapiocasSeeder::class,
            BebidasSeeder::class,
            SucosSeeder::class,
            AdicionaisSeeder::class,
            SystemSettingsSeeder::class,
        ]);
    }
}
