<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Historial;
use App\Models\Inventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /**
     * Inserta o actualiza inventario segun la combinacion producto-bodega.
     *
     * Datos de entrada (JSON):
     * - id_producto (integer, requerido, debe existir en productos.id)
     * - id_bodega (integer, requerido, debe existir en bodegas.id)
     * - cantidad (integer, requerido, min:1)
     * - created_by (integer, nullable, debe existir en users.id)
     * - updated_by (integer, nullable, debe existir en users.id)
     */
    public function store(Request $objRequest): JsonResponse
    {
        $arrValidated = $objRequest->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id'],
            'id_bodega' => ['required', 'integer', 'exists:bodegas,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $arrResultado = DB::transaction(function () use ($arrValidated) {
            $objInventario = Inventario::withTrashed()
                ->where('id_producto', $arrValidated['id_producto'])
                ->where('id_bodega', $arrValidated['id_bodega'])
                ->lockForUpdate()
                ->first();

            if (! $objInventario) {
                $objNuevoInventario = Inventario::create([
                    'id_producto' => $arrValidated['id_producto'],
                    'id_bodega' => $arrValidated['id_bodega'],
                    'cantidad' => $arrValidated['cantidad'],
                    'created_by' => $arrValidated['created_by'] ?? null,
                    'updated_by' => $arrValidated['updated_by'] ?? null,
                ]);

                $objHistorial = Historial::create([
                    'cantidad' => $arrValidated['cantidad'],
                    'id_bodega_origen' => null,
                    'id_bodega_destino' => $arrValidated['id_bodega'],
                    'id_inventario' => $objNuevoInventario->id,
                    'created_by' => $arrValidated['created_by'] ?? null,
                    'updated_by' => $arrValidated['updated_by'] ?? null,
                ]);

                return response()->json([
                    'message' => 'Registro de inventario creado.',
                    'accion' => 'insert',
                    'data' => $objNuevoInventario,
                    'historial' => $objHistorial,
                ], 201);
            }

            if ($objInventario->trashed()) {
                $objInventario->restore();
            }

            $objInventario->cantidad = $objInventario->cantidad + $arrValidated['cantidad'];
            $objInventario->updated_by = $arrValidated['updated_by'] ?? $objInventario->updated_by;
            $objInventario->save();

            $objHistorial = Historial::create([
                'cantidad' => $arrValidated['cantidad'],
                'id_bodega_origen' => null,
                'id_bodega_destino' => $arrValidated['id_bodega'],
                'id_inventario' => $objInventario->id,
                'created_by' => $arrValidated['created_by'] ?? null,
                'updated_by' => $arrValidated['updated_by'] ?? null,
            ]);

            return response()->json([
                'message' => 'Registro de inventario actualizado sumando la cantidad.',
                'accion' => 'update',
                'data' => $objInventario,
                'historial' => $objHistorial,
            ]);
        });

        return $arrResultado;
    }

    /**
     * Traslada unidades de un producto entre bodegas.
     *
     * Datos de entrada (JSON):
     * - id_producto (integer, requerido, debe existir en productos.id)
     * - id_bodega_origen (integer, requerido, debe existir en bodegas.id)
     * - id_bodega_destino (integer, requerido, diferente de id_bodega_origen, debe existir en bodegas.id)
     * - cantidad (integer, requerido, min:1)
     * - created_by (integer, nullable, debe existir en users.id)
     * - updated_by (integer, nullable, debe existir en users.id)
     */
    public function trasladar(Request $objRequest): JsonResponse
    {
        $arrValidated = $objRequest->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id'],
            'id_bodega_origen' => ['required', 'integer', 'exists:bodegas,id'],
            'id_bodega_destino' => ['required', 'integer', 'different:id_bodega_origen', 'exists:bodegas,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $arrResultado = DB::transaction(function () use ($arrValidated) {
            $objInventarioOrigen = Inventario::query()
                ->where('id_producto', $arrValidated['id_producto'])
                ->where('id_bodega', $arrValidated['id_bodega_origen'])
                ->lockForUpdate()
                ->first();

            if (! $objInventarioOrigen) {
                return response()->json([
                    'message' => 'No existe inventario del producto en la bodega de origen.',
                ], 422);
            }

            if ($objInventarioOrigen->cantidad < $arrValidated['cantidad']) {
                return response()->json([
                    'message' => 'Cantidad insuficiente en la bodega de origen para realizar el traslado.',
                    'disponible' => $objInventarioOrigen->cantidad,
                    'solicitado' => $arrValidated['cantidad'],
                ], 422);
            }

            $objInventarioDestino = Inventario::withTrashed()
                ->where('id_producto', $arrValidated['id_producto'])
                ->where('id_bodega', $arrValidated['id_bodega_destino'])
                ->lockForUpdate()
                ->first();

            $objInventarioOrigen->cantidad = $objInventarioOrigen->cantidad - $arrValidated['cantidad'];
            $objInventarioOrigen->updated_by = $arrValidated['updated_by'] ?? $objInventarioOrigen->updated_by;
            $objInventarioOrigen->save();

            if (! $objInventarioDestino) {
                $objInventarioDestino = Inventario::create([
                    'id_producto' => $arrValidated['id_producto'],
                    'id_bodega' => $arrValidated['id_bodega_destino'],
                    'cantidad' => $arrValidated['cantidad'],
                    'created_by' => $arrValidated['created_by'] ?? null,
                    'updated_by' => $arrValidated['updated_by'] ?? null,
                ]);
            } else {
                if ($objInventarioDestino->trashed()) {
                    $objInventarioDestino->restore();
                }

                $objInventarioDestino->cantidad = $objInventarioDestino->cantidad + $arrValidated['cantidad'];
                $objInventarioDestino->updated_by = $arrValidated['updated_by'] ?? $objInventarioDestino->updated_by;
                $objInventarioDestino->save();
            }

            $objHistorial = Historial::create([
                'cantidad' => $arrValidated['cantidad'],
                'id_bodega_origen' => $arrValidated['id_bodega_origen'],
                'id_bodega_destino' => $arrValidated['id_bodega_destino'],
                'id_inventario' => $objInventarioOrigen->id,
                'created_by' => $arrValidated['created_by'] ?? null,
                'updated_by' => $arrValidated['updated_by'] ?? null,
            ]);

            return response()->json([
                'message' => 'Traslado realizado correctamente.',
                'data' => [
                    'origen' => $objInventarioOrigen,
                    'destino' => $objInventarioDestino,
                    'historial' => $objHistorial,
                ],
            ]);
        });

        return $arrResultado;
    }
}
