<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo CarritoDetalle
 *
 * Representa un item/producto dentro del carrito de compras.
 * Almacena la cantidad y el precio unitario al momento de agregar.
 */
class CarritoDetalle extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'carrito_detalles';

    // Clave primaria personalizada
    protected $primaryKey = 'id_carrito_detalle';

    // Nombres personalizados de los timestamps
    const CREATED_AT = 'creado_en';

    const UPDATED_AT = null; // No tiene updated_at

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'id_carrito',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'creado_en',
    ];

    // Conversión de tipos
    protected $casts = [
        'id_carrito_detalle' => 'integer',
        'id_carrito' => 'integer',
        'id_producto' => 'integer',
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'creado_en' => 'datetime',
    ];

    /**
     * Relación: Un detalle pertenece a un carrito
     */
    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }

    /**
     * Relación: Un detalle pertenece a un producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * Calcular el subtotal de este item (cantidad * precio)
     */
    public function calcularSubtotal(): float
    {
        return $this->cantidad * $this->precio_unitario;
    }

    /**
     * Obtener la imagen principal del producto
     */
    public function getImagenAttribute()
    {
        return $this->producto?->imagenes()->where('es_principal', 1)->first();
    }
}
