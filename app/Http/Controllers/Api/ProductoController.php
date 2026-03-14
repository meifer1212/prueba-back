<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use App\Models\Historial;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Crea un producto y asigna stock inicial en la bodega por default.
     *
     * Datos de entrada (JSON):
     * - nombre (string, requerido, max:50)
     * - descripcion (string, nullable, max:300)
     * - estado (boolean, nullable)
     * - cantidad_inicial (integer, requerido, min:1)
     * - created_by (integer, nullable, debe existir en users.id)
     * - updated_by (integer, nullable, debe existir en users.id)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'estado' => ['nullable', 'boolean'],
            'cantidad_inicial' => ['required', 'integer', 'min:1'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $bodegaDefault = Bodega::query()
            ->where('estado', true)
            ->orderBy('id', 'asc')
            ->first();

        if (! $bodegaDefault) {
            return response()->json([
                'message' => 'No existe una bodega por default disponible.',
            ], 422);
        }

        $resultado = DB::transaction(function () use ($validated, $bodegaDefault) {
            $producto = Producto::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'estado' => $validated['estado'] ?? true,
                'created_by' => $validated['created_by'] ?? null,
                'updated_by' => $validated['updated_by'] ?? null,
            ]);

            $inventario = Inventario::create([
                'id_producto' => $producto->id,
                'id_bodega' => $bodegaDefault->id,
                'cantidad' => $validated['cantidad_inicial'],
                'created_by' => $validated['created_by'] ?? null,
                'updated_by' => $validated['updated_by'] ?? null,
            ]);

            $historial = Historial::create([
                'cantidad' => $validated['cantidad_inicial'],
                'id_bodega_origen' => null,
                'id_bodega_destino' => $bodegaDefault->id,
                'id_inventario' => $inventario->id,
                'created_by' => $validated['created_by'] ?? null,
                'updated_by' => $validated['updated_by'] ?? null,
            ]);

            return [
                'producto' => $producto,
                'inventario_inicial' => $inventario,
                'bodega_default' => $bodegaDefault,
                'historial' => $historial,
            ];
        });

        return response()->json([
            'message' => 'Producto creado y stock inicial asignado en bodega por default.',
            'data' => $resultado,
        ], 201);
    }


    /**
     * Lista todos los productos ordenados por total de stock disponible en todas las bodegas (de mayor a menor).
     * En caso de empate en el stock, se ordenan alfabéticamente por nombre
     * @return JsonResponse
     */
    public function indexByTotalDesc(): JsonResponse
    {
        $productos = Producto::query()
            ->leftJoin('inventarios', 'productos.id', '=', 'inventarios.id_producto')
            ->select('productos.*')
            ->selectRaw('COALESCE(SUM(inventarios.cantidad), 0) as Total')
            ->groupBy('productos.id')
            ->orderByDesc('Total')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json($productos);
    }
}
