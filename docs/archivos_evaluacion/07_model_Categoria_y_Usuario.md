# 🏷️ Modelos Eloquent: `Categoria.php` y `Usuario.php` — Categorización y Seguridad

## 1. Ficha Técnica

* **Ubicaciones:**
  * `app/Models/Categoria.php`
  * `app/Models/Usuario.php`
* **Tipo:** Modelos Eloquent ORM (Usuario extiende de `Illuminate\Foundation\Auth\User`).
* **Capa:** Modelo / Autenticación (M en MVC).
* **Propósito:** 
  * `Categoria.php`: Gestionar la taxonomía del catálogo, scope de categorías activas y conteo dinámico de productos.
  * `Usuario.php`: Administrar las credenciales de los usuarios, integración con el guard de autenticación de Laravel (`Authenticatable`), verificación de roles con `isAdmin()` y concatenación de nombre completo.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    subgraph "Modelo Categoria"
        Cat[Categoria.php] -->|hasMany| Subcat[Subcategoria.php]
        Cat -->|hasMany| Prod[Producto.php]
        ControllerTienda[TiendaController.php] -->|Categoria::activas()->withCount('productos')| Cat
    end

    subgraph "Modelo Usuario (Authenticatable)"
        User[Usuario.php] -->|belongsTo| Role[Rol.php]
        User -->|hasMany| Orders[Pedido.php]
        User -->|hasMany| Addresses[Direccion.php]
        AuthCtrl[AuthController.php] -->|Auth::login($usuario)| User
        AdminMid[AdminMiddleware.php] -->|auth()->user()->isAdmin()| User
    end
```

---

## 3. Código Fuente y Explicación Detallada: `Usuario.php`

```php
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
        'id_rol', 'nombre', 'apellido', 'email',
        'password_hash', 'telefono', 'activo', 'email_verificado',
    ];

    // Oculta campos sensibles en serializaciones JSON
    protected $hidden = [
        'password_hash', 'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verificado' => 'boolean',
        'ultimo_acceso' => 'datetime',
    ];

    // 1. Vinculación con el sistema de contraseñas de Laravel
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // 2. Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    // 3. Accesor de Nombre Completo
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    // 4. Verificación de Rol Administrativo
    public function isAdmin()
    {
        return $this->id_rol == 1;
    }
}
```

### Análisis Técnico de `Usuario.php`:
1. **Extensión `Authenticatable`:** Permite que este modelo sustituya al `User` predeterminado de Laravel, integrándose con `Auth::user()`, `Auth::check()`, `Auth::login()`, cookies y sesiones.
2. **Método `getAuthPassword()`:** Laravel busca por defecto una columna llamada `password`. Mediante este método abstracto sobrescrito, se instruye al framework para que tome el hash de la columna existente `password_hash`.
3. **Propiedad `$hidden`:** Garantiza que si el modelo se envía en una respuesta AJAX/API o se serializa a JSON, nunca se fugue el hash de la contraseña ni el token de sesión.
4. **Accesor `nombre_completo`:** Permite invocar `$usuario->nombre_completo` en las vistas Blade para mostrar el saludo o la cabecera del perfil.

---

## 4. Código Fuente y Explicación Detallada: `Categoria.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class, 'id_categoria', 'id_categoria');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', 1);
    }
}
```

### Análisis Técnico de `Categoria.php`:
1. **Scope Local `scopeActivas`:** Filtra categorías donde `activo = 1`.
2. **Relación `productos()`:** Establece que una categoría agrupa muchos productos (`hasMany`). Esto habilita en el controlador la potente instrucción `Categoria::activas()->withCount('productos')->get()`.

---

## 5. Preguntas Frecuentes para Evaluación

1. **¿Cómo se configuró Laravel para que use la tabla `usuarios` en vez de `users`?**
   * *Respuesta:* En `config/auth.php`, dentro de `'providers' => ['users' => ['model' => App\Models\Usuario::class]]`, y dentro del modelo especificando `protected $table = 'usuarios';`.
2. **¿Por qué `Usuario.php` no usa timestamps automáticos de Laravel?**
   * *Respuesta:* Porque la tabla original en MySQL define `creado_en` y `actualizado_en` (o es gestionada manualmente). Declarar `public $timestamps = false;` o mapear `const CREATED_AT = 'creado_en';` evita que Laravel intente escribir en las columnas en inglés `created_at` y `updated_at`.
3. **¿Qué ventaja ofrece el método `isAdmin()` en el modelo frente a preguntar `$usuario->id_rol == 1` en cada vista?**
   * *Respuesta:* Encapsulamiento y mantenibilidad. Si mañana los administradores se definen por un rol con ID 5 o por múltiples roles, solo se modifica el método `isAdmin()` en `Usuario.php` y todo el sistema (controladores, middlewares y vistas) queda actualizado inmediatamente.
