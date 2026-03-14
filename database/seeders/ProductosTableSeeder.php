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
        $faker = Factory::create();
        $userIds = User::query()->pluck('id')->all();
        $totalProductos = rand(60, 120);

        for ($i = 1; $i <= $totalProductos; $i++) {
            $createdBy = !empty($userIds) ? $userIds[array_rand($userIds)] : null;
            $updatedBy = !empty($userIds) ? $userIds[array_rand($userIds)] : null;

            Producto::create([
                'nombre' => substr('Producto ' . strtoupper($faker->unique()->bothify('??###??')), 0, 50),
                'descripcion' => substr($faker->sentence(rand(6, 14)), 0, 300),
                'estado' => $faker->boolean(80),
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);
        }
    }
}
