<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Carrito
 *
 * Representa el carrito de compras de un usuario.
 * Un carrito puede estar activo o completado cuando se convierte en pedido.
 */
class Carrito extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'carritos';

    // Clave primaria personalizada
    protected $primaryKey = 'id_carrito';

    // Nombres personalizados de los timestamps
    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'id_usuario',
        'estado', // 'activo', 'completado', 'abandonado'
        'codigo_descuento_id',
        'descuento_aplicado',
        'creado_en',
        'actualizado_en',
    ];

    // Conversión de tipos
    protected $casts = [
        'id_carrito' => 'integer',
        'id_usuario' => 'integer',
        'codigo_descuento_id' => 'integer',
        'descuento_aplicado' => 'decimal:2',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Relación: Un carrito pertenece a un usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación: Un carrito tiene muchos detalles (items)
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(CarritoDetalle::class, 'id_carrito', 'id_carrito');
    }

    /**
     * Relación: Un carrito puede tener un código de descuento aplicado
     */
    public function codigoDescuento(): BelongsTo
    {
        return $this->belongsTo(CodigoDescuento::class, 'codigo_descuento_id', 'id_codigo_descuento');
    }

    /**
     * Scope: Obtener solo carritos activos
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Calcular el subtotal del carrito
     */
    public function calcularSubtotal(): float
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->cantidad * $detalle->precio_unitario;
        });
    }

    /**
     * Contar cantidad total de items en el carrito
     */
    public function contarItems(): int
    {
        return $this->detalles->sum('cantidad');
    }

    /**
     * Calcular el total del carrito (subtotal - descuento)
     */
    public function calcularTotal(): float
    {
        $subtotal = $this->calcularSubtotal();
        $descuento = $this->descuento_aplicado ?? 0;

        return max(0, $subtotal - $descuento);
    }
}
