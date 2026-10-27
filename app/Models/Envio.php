<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Envio
 *
 * Representa información de envío y tracking de pedidos.
 */
class Envio extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'envios';

    // Clave primaria personalizada
    protected $primaryKey = 'id_envio';

    // Nombres personalizados de los timestamps
    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'id_pedido',
        'transportadora',
        'numero_guia',
        'estado',
        'fecha_despacho',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
        'observaciones',
        'creado_en',
        'actualizado_en',
    ];

    // Conversión de tipos
    protected $casts = [
        'id_envio' => 'integer',
        'id_pedido' => 'integer',
        'fecha_despacho' => 'datetime',
        'fecha_entrega_estimada' => 'datetime',
        'fecha_entrega_real' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Relación: Un envío pertenece a un pedido
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Scope: Obtener envíos en tránsito
     */
    public function scopeEnTransito($query)
    {
        return $query->where('estado', 'en_transito');
    }

    /**
     * Scope: Obtener envíos entregados
     */
    public function scopeEntregados($query)
    {
        return $query->where('estado', 'entregado');
    }
}
