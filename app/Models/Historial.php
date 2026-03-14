<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Historial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historiales';

    protected $fillable = [
        'cantidad',
        'id_bodega_origen',
        'id_bodega_destino',
        'id_inventario',
        'created_by',
        'updated_by',
    ];

    public function bodegaOrigen()
    {
        return $this->belongsTo(Bodega::class, 'id_bodega_origen');
    }

    public function bodegaDestino()
    {
        return $this->belongsTo(Bodega::class, 'id_bodega_destino');
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'id_inventario');
    }
}
