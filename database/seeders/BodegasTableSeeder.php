<?php

namespace Database\Seeders;

use App\Models\Bodega;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class BodegasTableSeeder extends Seeder
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

        if (empty($userIds)) {
            return;
        }

        $totalBodegas = rand(8, 20);

        for ($i = 1; $i <= $totalBodegas; $i++) {
            $responsable = $userIds[array_rand($userIds)];
            $createdBy = $userIds[array_rand($userIds)];
            $updatedBy = $userIds[array_rand($userIds)];

            Bodega::create([
                'nombre' => substr('Bodega ' . strtoupper($faker->unique()->bothify('??-###')), 0, 30),
                'id_responsable' => $responsable,
                'estado' => $faker->boolean(60),
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);
        }
    }
}
