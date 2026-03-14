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
        $objFaker = Factory::create();
        $arrUserIds = User::query()->pluck('id')->all();

        if (empty($arrUserIds)) {
            return;
        }

        $numTotalBodegas = rand(8, 20);

        for ($numIndice = 1; $numIndice <= $numTotalBodegas; $numIndice++) {
            $numIdResponsable = $arrUserIds[array_rand($arrUserIds)];
            $createdBy = $arrUserIds[array_rand($arrUserIds)];
            $updatedBy = $arrUserIds[array_rand($arrUserIds)];

            Bodega::create([
                'nombre' => substr('Bodega ' . strtoupper($objFaker->unique()->bothify('??-###')), 0, 30),
                'id_responsable' => $numIdResponsable,
                'estado' => $objFaker->boolean(60),
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);
        }
    }
}
