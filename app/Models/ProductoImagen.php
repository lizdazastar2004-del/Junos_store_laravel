<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagenes';

    protected $primaryKey = 'id_imagen';

    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'ruta_imagen',
        'url_imagen',
        'es_principal',
        'principal',
        'orden',
        'orden_imagen',
        'texto_alternativo',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'principal' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function getRutaImagenAttribute()
    {
        return $this->attributes['ruta_imagen'] ?? $this->attributes['url_imagen'] ?? null;
    }

    public function getEsPrincipalAttribute()
    {
        return (bool) ($this->attributes['principal'] ?? $this->attributes['es_principal'] ?? false);
    }

    public function getUrlLimpiaAttribute()
    {
        $raw = $this->ruta_imagen;
        if (! $raw) {
            return null;
        }

        // Remover prefijo /public/ si viene de la migración PHP anterior
        return preg_replace('#^/?public/#', '/', $raw);
    }
}
