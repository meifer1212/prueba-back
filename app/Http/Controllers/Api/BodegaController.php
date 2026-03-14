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
        $colBodegas = Bodega::orderBy('nombre', 'asc')->get();

        return response()->json($colBodegas);
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
    public function store(Request $objRequest): JsonResponse
    {
        $arrValidated = $objRequest->validate([
            'nombre' => ['required', 'string', 'max:30'],
            'id_responsable' => ['required', 'integer', 'exists:users,id'],
            'estado' => ['nullable', 'boolean'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $objBodega = Bodega::create([
            'nombre' => $arrValidated['nombre'],
            'id_responsable' => $arrValidated['id_responsable'],
            'estado' => $arrValidated['estado'] ?? true,
            'created_by' => $arrValidated['created_by'] ?? null,
            'updated_by' => $arrValidated['updated_by'] ?? null,
        ]);

        return response()->json($objBodega, 201);
    }
}
