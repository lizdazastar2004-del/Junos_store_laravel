<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ==============================================================================
 * MODELO: Direccion
 * ==============================================================================
 * Representa una dirección de envío registrada por un cliente.
 *
 * Tabla en BD: 'direcciones'
 * Clave primaria: 'id_direccion'
 * Clave foránea: 'id_usuario' -> usuarios(id_usuario)
 * ==============================================================================
 */
class Direccion extends Model
{
    /** @var string Nombre exacto de la tabla física en MySQL */
    protected $table = 'direcciones';

    /** @var string Nombre de la clave primaria personalizada */
    protected $primaryKey = 'id_direccion';

    /** @var bool Habilita timestamps personalizados */
    public $timestamps = true;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    /**
     * Atributos que se pueden asignar masivamente con create() o update().
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_usuario',
        'calle',
        'ciudad',
        'departamento',
        'codigo_postal',
        'referencia',
        'principal',
        'activo',
    ];

    /**
     * Conversión automática de tipos de datos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'principal' => 'boolean',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Relación Inversa: Una dirección pertenece a un único Usuario (Cliente).
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Accesor para obtener la dirección completa formateada en un solo string.
     */
    public function getDireccionCompletaAttribute(): string
    {
        $partes = array_filter([
            $this->calle,
            $this->ciudad,
            $this->departamento,
            $this->codigo_postal ? 'CP: '.$this->codigo_postal : null,
        ]);

        return implode(', ', $partes);
    }
}
