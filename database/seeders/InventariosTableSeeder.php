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
        $arrBodegaIds = Bodega::query()->pluck('id')->all();
        $arrProductoIds = Producto::query()->pluck('id')->all();
        $arrUserIds = User::query()->pluck('id')->all();

        if (empty($arrBodegaIds) || empty($arrProductoIds)) {
            return;
        }

        $arrCombinaciones = [];

        foreach ($arrBodegaIds as $numIdBodega) {
            foreach ($arrProductoIds as $numIdProducto) {
                $arrCombinaciones[] = [
                    'id_bodega' => $numIdBodega,
                    'id_producto' => $numIdProducto,
                ];
            }
        }

        shuffle($arrCombinaciones);

        $numMeta = min(count($arrCombinaciones), rand(90, 260));
        $arrSeleccionadas = array_slice($arrCombinaciones, 0, $numMeta);

        foreach ($arrSeleccionadas as $arrCombinacion) {
            $createdBy = !empty($arrUserIds) ? $arrUserIds[array_rand($arrUserIds)] : null;
            $updatedBy = !empty($arrUserIds) ? $arrUserIds[array_rand($arrUserIds)] : null;
            $numCantidad = rand(10, 500);

            $objInventario = Inventario::updateOrCreate(
                [
                    'id_bodega' => $arrCombinacion['id_bodega'],
                    'id_producto' => $arrCombinacion['id_producto'],
                ],
                [
                    'cantidad' => $numCantidad,
                    'created_by' => $createdBy,
                    'updated_by' => $updatedBy,
                ]
            );

            Historial::create([
                'cantidad' => $numCantidad,
                'id_bodega_origen' => null,
                'id_bodega_destino' => $arrCombinacion['id_bodega'],
                'id_inventario' => $objInventario->id,
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
            ]);
        }
    }
}
