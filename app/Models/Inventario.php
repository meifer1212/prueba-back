<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventarios';

    protected $fillable = [
        'id_bodega',
        'id_producto',
        'cantidad',
        'created_by',
        'updated_by',
    ];

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'id_bodega');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function historiales()
    {
        return $this->hasMany(Historial::class, 'id_inventario');
    }
}
