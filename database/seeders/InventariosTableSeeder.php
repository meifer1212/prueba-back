<?php

namespace Database\Seeders;

use App\Models\Bodega;
use App\Models\Historial;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventariosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bodegaIds = Bodega::query()->pluck('id')->all();
        $productoIds = Producto::query()->pluck('id')->all();
        $userIds = User::query()->pluck('id')->all();

        if (empty($bodegaIds) || empty($productoIds)) {
            return;
        }

        $combinaciones = [];

        foreach ($bodegaIds as $idBodega) {
            foreach ($productoIds as $idProducto) {
                $combinaciones[] = [
                    'id_bodega' => $idBodega,
                    'id_producto' => $idProducto,
                ];
            }
        }

        shuffle($combinaciones);

        $meta = min(count($combinaciones), rand(90, 260));
        $seleccionadas = array_slice($combinaciones, 0, $meta);

        foreach ($seleccionadas as $combinacion) {
            $createdBy = !empty($userIds) ? $userIds[array_rand($userIds)] : null;
            $updatedBy = !empty($userIds) ? $userIds[array_rand($userIds)] : null;
            $cantidad = rand(10, 500);

            $inventario = Inventario::updateOrCreate(
                [
                    'id_bodega' => $combinacion['id_bodega'],
                    'id_producto' => $combinacion['id_producto'],
                ],
                [
                    'cantidad' => $cantidad,
                    'created_by' => $createdBy,
                    'updated_by' => $updatedBy,
                ]
            );

            Historial::create([
                'cantidad' => $cantidad,
                'id_bodega_origen' => null,
                'id_bodega_destino' => $combinacion['id_bodega'],
                'id_inventario' => $inventario->id,
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);
        }
    }
}
