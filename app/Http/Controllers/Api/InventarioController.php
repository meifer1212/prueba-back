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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id'],
            'id_bodega' => ['required', 'integer', 'exists:bodegas,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $resultado = DB::transaction(function () use ($validated) {
            $inventario = Inventario::withTrashed()
                ->where('id_producto', $validated['id_producto'])
                ->where('id_bodega', $validated['id_bodega'])
                ->lockForUpdate()
                ->first();

            if (! $inventario) {
                $nuevoInventario = Inventario::create([
                    'id_producto' => $validated['id_producto'],
                    'id_bodega' => $validated['id_bodega'],
                    'cantidad' => $validated['cantidad'],
                    'created_by' => $validated['created_by'] ?? null,
                    'updated_by' => $validated['updated_by'] ?? null,
                ]);

                $historial = Historial::create([
                    'cantidad' => $validated['cantidad'],
                    'id_bodega_origen' => null,
                    'id_bodega_destino' => $validated['id_bodega'],
                    'id_inventario' => $nuevoInventario->id,
                    'created_by' => $validated['created_by'] ?? null,
                    'updated_by' => $validated['updated_by'] ?? null,
                ]);

                return response()->json([
                    'message' => 'Registro de inventario creado.',
                    'accion' => 'insert',
                    'data' => $nuevoInventario,
                    'historial' => $historial,
                ], 201);
            }

            if ($inventario->trashed()) {
                $inventario->restore();
            }

            $inventario->cantidad = $inventario->cantidad + $validated['cantidad'];
            $inventario->updated_by = $validated['updated_by'] ?? $inventario->updated_by;
            $inventario->save();

            $historial = Historial::create([
                'cantidad' => $validated['cantidad'],
                'id_bodega_origen' => null,
                'id_bodega_destino' => $validated['id_bodega'],
                'id_inventario' => $inventario->id,
                'created_by' => $validated['created_by'] ?? null,
                'updated_by' => $validated['updated_by'] ?? null,
            ]);

            return response()->json([
                'message' => 'Registro de inventario actualizado sumando la cantidad.',
                'accion' => 'update',
                'data' => $inventario,
                'historial' => $historial,
            ]);
        });

        return $resultado;
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
    public function trasladar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id'],
            'id_bodega_origen' => ['required', 'integer', 'exists:bodegas,id'],
            'id_bodega_destino' => ['required', 'integer', 'different:id_bodega_origen', 'exists:bodegas,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $resultado = DB::transaction(function () use ($validated) {
            $inventarioOrigen = Inventario::query()
                ->where('id_producto', $validated['id_producto'])
                ->where('id_bodega', $validated['id_bodega_origen'])
                ->lockForUpdate()
                ->first();

            if (! $inventarioOrigen) {
                return response()->json([
                    'message' => 'No existe inventario del producto en la bodega de origen.',
                ], 422);
            }

            if ($inventarioOrigen->cantidad < $validated['cantidad']) {
                return response()->json([
                    'message' => 'Cantidad insuficiente en la bodega de origen para realizar el traslado.',
                    'disponible' => $inventarioOrigen->cantidad,
                    'solicitado' => $validated['cantidad'],
                ], 422);
            }

            $inventarioDestino = Inventario::withTrashed()
                ->where('id_producto', $validated['id_producto'])
                ->where('id_bodega', $validated['id_bodega_destino'])
                ->lockForUpdate()
                ->first();

            $inventarioOrigen->cantidad = $inventarioOrigen->cantidad - $validated['cantidad'];
            $inventarioOrigen->updated_by = $validated['updated_by'] ?? $inventarioOrigen->updated_by;
            $inventarioOrigen->save();

            if (! $inventarioDestino) {
                $inventarioDestino = Inventario::create([
                    'id_producto' => $validated['id_producto'],
                    'id_bodega' => $validated['id_bodega_destino'],
                    'cantidad' => $validated['cantidad'],
                    'created_by' => $validated['created_by'] ?? null,
                    'updated_by' => $validated['updated_by'] ?? null,
                ]);
            } else {
                if ($inventarioDestino->trashed()) {
                    $inventarioDestino->restore();
                }

                $inventarioDestino->cantidad = $inventarioDestino->cantidad + $validated['cantidad'];
                $inventarioDestino->updated_by = $validated['updated_by'] ?? $inventarioDestino->updated_by;
                $inventarioDestino->save();
            }

            $historial = Historial::create([
                'cantidad' => $validated['cantidad'],
                'id_bodega_origen' => $validated['id_bodega_origen'],
                'id_bodega_destino' => $validated['id_bodega_destino'],
                'id_inventario' => $inventarioOrigen->id,
                'created_by' => $validated['created_by'] ?? null,
                'updated_by' => $validated['updated_by'] ?? null,
            ]);

            return response()->json([
                'message' => 'Traslado realizado correctamente.',
                'data' => [
                    'origen' => $inventarioOrigen,
                    'destino' => $inventarioDestino,
                    'historial' => $historial,
                ],
            ]);
        });

        return $resultado;
    }
}
