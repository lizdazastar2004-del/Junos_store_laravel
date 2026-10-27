<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Pedido
 *
 * Representa un pedido realizado por un cliente.
 * Estados: pendiente, confirmado, en_preparacion, enviado, entregado, cancelado
 */
class Pedido extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'pedidos';

    // Clave primaria personalizada
    protected $primaryKey = 'id_pedido';

    // Nombres personalizados de los timestamps
    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    // Deshabilitar timestamps automáticos
    public $timestamps = false;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'numero_pedido',
        'id_usuario',
        'id_direccion',
        'subtotal',
        'descuento',
        'costo_envio',
        'total',
        'estado',
        'direccion_entrega',
        'notas',
        'observaciones',
        'codigo_descuento_id',
        'fecha_pedido',
        'fecha_entrega',
        'fecha_entrega_estimada',
        'creado_en',
        'actualizado_en',
    ];

    // Conversión de tipos
    protected $casts = [
        'id_pedido' => 'integer',
        'id_usuario' => 'integer',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'total' => 'decimal:2',
        'codigo_descuento_id' => 'integer',
        'fecha_entrega_estimada' => 'date',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Boot method para establecer timestamps manualmente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->creado_en = now();
            $model->actualizado_en = now();
        });

        static::updating(function ($model) {
            $model->actualizado_en = now();
        });
    }

    /**
     * Relación: Un pedido pertenece a un usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación: Un pedido tiene muchos detalles (items)
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Relación: Un pedido tiene un pago
     */
    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Relación: Un pedido puede tener un código de descuento aplicado
     */
    public function codigoDescuento(): BelongsTo
    {
        return $this->belongsTo(CodigoDescuento::class, 'codigo_descuento_id', 'id_codigo_descuento');
    }

    /**
     * Scope: Obtener pedidos por estado
     */
    public function scopeEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope: Obtener pedidos pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope: Obtener pedidos confirmados
     */
    public function scopeConfirmados($query)
    {
        return $query->where('estado', 'confirmado');
    }

    /**
     * Obtener el estado formateado
     */
    public function getEstadoFormateadoAttribute(): string
    {
        $estado = strtolower($this->estado ?? 'pendiente');

        $estados = config('constants.ESTADOS_PEDIDO', [
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado',
            'pagado' => 'Pagado',
            'en_proceso' => 'En Proceso',
            'en_preparacion' => 'En Preparación',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ]);

        return $estados[$estado] ?? ucfirst(str_replace('_', ' ', $this->estado));
    }

    /**
     * Obtener el color del badge según el estado
     */
    public function getEstadoColorAttribute(): string
    {
        $estado = strtolower($this->estado ?? 'pendiente');

        $colores = [
            'pendiente' => '#f39c12',
            'confirmado' => '#3498db',
            'pagado' => '#27ae60',
            'en_proceso' => '#9b59b6',
            'en_preparacion' => '#9b59b6',
            'enviado' => '#1abc9c',
            'entregado' => '#2ecc71',
            'cancelado' => '#e74c3c',
        ];

        return $colores[$estado] ?? '#9b84cc';
    }
}
