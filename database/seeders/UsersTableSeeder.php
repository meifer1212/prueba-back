<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objFaker = Factory::create();
        $numTotalUsers = rand(25, 60);

        $objPrimerUsuario = User::create([
            'nombre' => substr($objFaker->name(), 0, 50),
            'foto' => 'users/' . $objFaker->uuid() . '.png',
            'estado' => true,
            'created_by' => null,
            'updated_by' => null,
        ]);

        $arrUserIds = [$objPrimerUsuario->id];

        for ($numIndice = 2; $numIndice <= $numTotalUsers; $numIndice++) {
            $createdBy = $arrUserIds[array_rand($arrUserIds)];
            $updatedBy = $arrUserIds[array_rand($arrUserIds)];

            $objNuevoUsuario = User::create([
                'nombre' => substr($objFaker->unique()->name(), 0, 50),
                'foto' => 'users/' . $objFaker->uuid() . '.png',
                'estado' => $objFaker->boolean(90),
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);

            $arrUserIds[] = $objNuevoUsuario->id;
        }
    }
}
