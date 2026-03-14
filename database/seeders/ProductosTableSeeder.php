<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ProductosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objFaker = Factory::create();
        $arrUserIds = User::query()->pluck('id')->all();
        $numTotalProductos = rand(60, 120);

        for ($numIndice = 1; $numIndice <= $numTotalProductos; $numIndice++) {
            $createdBy = !empty($arrUserIds) ? $arrUserIds[array_rand($arrUserIds)] : null;
            $updatedBy = !empty($arrUserIds) ? $arrUserIds[array_rand($arrUserIds)] : null;

            Producto::create([
                'nombre' => substr('Producto ' . strtoupper($objFaker->unique()->bothify('??###??')), 0, 50),
                'descripcion' => substr($objFaker->sentence(rand(6, 14)), 0, 300),
                'estado' => $objFaker->boolean(80),
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);
        }
    }
}
