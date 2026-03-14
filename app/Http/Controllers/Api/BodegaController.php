<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BodegaController extends Controller
{
    /**
     * Lista todas las bodegas ordenadas por nombre.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $bodegas = Bodega::orderBy('nombre', 'asc')->get();

        return response()->json($bodegas);
    }

    /**
     * Crea una nueva bodega.
     *
     * Datos de entrada (JSON):
     * - nombre (string, requerido, max:30)
     * - id_responsable (integer, requerido, debe existir en users.id)
     * - estado (boolean, opcional, por defecto true)
     * - created_by (integer, opcional)
     * - updated_by (integer, opcional)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:30'],
            'id_responsable' => ['required', 'integer', 'exists:users,id'],
            'estado' => ['nullable', 'boolean'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $bodega = Bodega::create([
            'nombre' => $validated['nombre'],
            'id_responsable' => $validated['id_responsable'],
            'estado' => $validated['estado'] ?? true,
            'created_by' => $validated['created_by'] ?? null,
            'updated_by' => $validated['updated_by'] ?? null,
        ]);

        return response()->json($bodega, 201);
    }
}
