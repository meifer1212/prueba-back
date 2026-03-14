<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bodega extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bodegas';

    protected $fillable = [
        'nombre',
        'id_responsable',
        'estado',
        'created_by',
        'updated_by',
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'id_responsable');
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_bodega');
    }

    public function historialesOrigen()
    {
        return $this->hasMany(Historial::class, 'id_bodega_origen');
    }

    public function historialesDestino()
    {
        return $this->hasMany(Historial::class, 'id_bodega_destino');
    }
}
