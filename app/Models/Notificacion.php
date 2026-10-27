<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Notificacion
 *
 * Representa notificaciones del sistema para usuarios.
 * Tipos: pedido_nuevo, nuevo_pedido (admin), pedido_enviado, etc.
 */
class Notificacion extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'notificaciones';

    // Clave primaria personalizada
    protected $primaryKey = 'id_notificacion';

    // Nombres personalizados de los timestamps
    const CREATED_AT = 'creado_en';

    const UPDATED_AT = null; // No tiene updated_at

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'id_usuario',
        'tipo',
        'titulo',
        'mensaje',
        'leida',
        'creado_en',
    ];

    // Conversión de tipos
    protected $casts = [
        'id_notificacion' => 'integer',
        'id_usuario' => 'integer',
        'leida' => 'boolean',
        'creado_en' => 'datetime',
    ];

    /**
     * Relación: Una notificación pertenece a un usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Scope: Obtener solo notificaciones no leídas
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', 0);
    }

    /**
     * Scope: Obtener solo notificaciones leídas
     */
    public function scopeLeidas($query)
    {
        return $query->where('leida', 1);
    }

    /**
     * Marcar la notificación como leída
     */
    public function marcarComoLeida(): void
    {
        $this->leida = 1;
        $this->save();
    }
}
