<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo CodigoDescuento
 *
 * Representa códigos promocionales que los usuarios pueden aplicar
 * a sus carritos para obtener descuentos.
 */
class CodigoDescuento extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'codigos_descuento';

    // Clave primaria personalizada
    protected $primaryKey = 'id_codigo_descuento';

    // Nombres personalizados de los timestamps
    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'codigo',
        'tipo', // 'porcentaje' o 'fijo'
        'valor',
        'monto_minimo',
        'fecha_inicio',
        'fecha_fin',
        'usos_maximos',
        'usos_actuales',
        'activo',
        'creado_en',
        'actualizado_en',
    ];

    // Conversión de tipos
    protected $casts = [
        'id_codigo_descuento' => 'integer',
        'valor' => 'decimal:2',
        'monto_minimo' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'usos_maximos' => 'integer',
        'usos_actuales' => 'integer',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Scope: Obtener solo códigos activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', 1);
    }

    /**
     * Scope: Obtener códigos vigentes (dentro del rango de fechas)
     */
    public function scopeVigente($query)
    {
        $now = Carbon::now();

        return $query->where('fecha_inicio', '<=', $now)
            ->where('fecha_fin', '>=', $now);
    }

    /**
     * Verificar si el código es válido para usar
     */
    public function esValido(): bool
    {
        // Verificar si está activo
        if (! $this->activo) {
            return false;
        }

        // Verificar si está dentro del rango de fechas
        $now = Carbon::now();
        if ($this->fecha_inicio > $now || $this->fecha_fin < $now) {
            return false;
        }

        // Verificar si no ha excedido los usos máximos
        if ($this->usos_maximos > 0 && $this->usos_actuales >= $this->usos_maximos) {
            return false;
        }

        return true;
    }

    /**
     * Calcular el monto de descuento según el subtotal
     */
    public function calcularDescuento(float $subtotal): float
    {
        // Verificar monto mínimo
        if ($subtotal < $this->monto_minimo) {
            return 0;
        }

        if ($this->tipo === 'porcentaje') {
            // Descuento porcentual
            return ($subtotal * $this->valor) / 100;
        } else {
            // Descuento fijo
            return min($this->valor, $subtotal);
        }
    }

    /**
     * Incrementar el contador de usos
     */
    public function incrementarUsos(): void
    {
        $this->increment('usos_actuales');
    }

    /**
     * Buscar código por su string
     */
    public static function buscarPorCodigo(string $codigo): ?self
    {
        return self::where('codigo', strtoupper($codigo))->first();
    }
}
