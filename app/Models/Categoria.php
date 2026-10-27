<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $primaryKey = 'id_categoria';

    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class, 'id_categoria', 'id_categoria');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', 1);
    }
}
