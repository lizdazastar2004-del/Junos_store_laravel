<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'id_categoria',
        'id_subcategoria',
        'id_marca',
        'sku',
        'stock_actual',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_reservado' => 'integer',
        'stock_minimo' => 'integer',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class, 'id_producto', 'id_producto');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'id_producto', 'id_producto');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    public function scopeConStock($query)
    {
        return $query->where('stock_actual', '>', 0);
    }
}
