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
        $faker = Factory::create();
        $totalUsers = rand(25, 60);

        $primerUsuario = User::create([
            'nombre' => substr($faker->name(), 0, 50),
            'foto' => 'users/' . $faker->uuid() . '.png',
            'estado' => true,
            'created_by' => null,
            'updated_by' => null,
        ]);

        $userIds = [$primerUsuario->id];

        for ($i = 2; $i <= $totalUsers; $i++) {
            $createdBy = $userIds[array_rand($userIds)];
            $updatedBy = $userIds[array_rand($userIds)];

            $nuevoUsuario = User::create([
                'nombre' => substr($faker->unique()->name(), 0, 50),
                'foto' => 'users/' . $faker->uuid() . '.png',
                'estado' => $faker->boolean(90),
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);

            $userIds[] = $nuevoUsuario->id;
        }
    }
}
