<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';

    protected $primaryKey = 'id_resena';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'id_producto',
        'id_usuario',
        'id_pedido',
        'calificacion',
        'comentario',
        'activo',
        'verificado',
        'creado_en',
        'actualizado_en',
    ];

    protected $casts = [
        'calificacion' => 'integer',
        'verificado' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
