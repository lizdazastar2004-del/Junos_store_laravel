<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_rol',
        'nombre',
        'apellido',
        'email',
        'password_hash',
        'telefono',
        'activo',
        'email_verificado',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verificado' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    // Laravel usa 'password' por defecto, pero nuestra tabla tiene 'password_hash'
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_usuario', 'id_usuario');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_usuario', 'id_usuario');
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'id_usuario', 'id_usuario');
    }

    public function carritoActivo()
    {
        return $this->hasOne(Carrito::class, 'id_usuario', 'id_usuario')
            ->where('estado', 'activo');
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function isAdmin()
    {
        return $this->id_rol == 1;
    }
}
