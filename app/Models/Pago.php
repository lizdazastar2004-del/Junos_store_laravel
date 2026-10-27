<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $primaryKey = 'id_pago';

    public $timestamps = false;

    const CREATED_AT = 'fecha_pago';

    protected $fillable = [
        'id_pedido',
        'id_metodo_pago',
        'monto',
        'estado',
        'referencia',
        'referencia_externa',
        'fecha_transaccion',
        'fecha_pago',
        'confirmado_por',
        'fecha_confirmacion',
        'observaciones',
        'datos_adicionales',
        'creado_en',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago', 'id_metodo_pago');
    }
}
