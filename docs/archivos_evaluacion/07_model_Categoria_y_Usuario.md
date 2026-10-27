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
        ControllerTienda[TiendaController.php] -->|Categoria::activas conCount productos| Cat
        Cat --> MySQLCat[(MySQL: categorias)]
    end

    subgraph "Modelo Usuario (Authenticatable)"
        User[Usuario.php] -->|belongsTo| Role[Rol.php]
        User -->|hasMany| Orders[Pedido.php]
        User -->|hasMany| Addresses[Direccion.php]
        AuthCtrl[AuthController.php] -->|Auth::login usuario| User
        AdminMid[AdminMiddleware.php] -->|auth user isAdmin| User
        User --> MySQLUser[(MySQL: usuarios)]
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
        return (int) $this->id_rol === 1;
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

---

## 5. 💥 ANÁLISIS DE IMPACTO: "¿QUÉ SUCEDE SI QUITO ESTO EN EL PROYECTO?"

| Si quitas... | Error técnico que ocurre | Impacto real en el sistema |
| :--- | :--- | :--- |
| **`extends Authenticatable` en `Usuario.php`** | `TypeError: Argument 1 passed to Illuminate\Auth\SessionGuard::login() must implement Illuminate\Contracts\Auth\Authenticatable` | El sistema de autenticación de Laravel colapsa por completo. Es imposible iniciar sesión, registrarse o recordar sesiones. |
| **`getAuthPassword()` en `Usuario.php`** | `ErrorException: Undefined property: App\Models\Usuario::$password` o autenticación siempre fallida. | Laravel intentará validar la contraseña buscando `$usuario->password`. Al ser nulo o no existir, ningún usuario (ni admin ni clientes) podrá iniciar sesión en la tienda. |
| **`protected $table = 'usuarios';`** | `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'junos_store.users' doesn't exist` | Laravel asume por convención en inglés que el modelo `Usuario` corresponde a la tabla `users` (pluralizada en inglés). Al no existir dicha tabla, se produce un error 500 al consultar usuarios. |
| **`protected $hidden = ['password_hash', ...]`** | Ningún error visible inmediato. | **Fuga de seguridad en APIs y JSON:** Si se envía el objeto de usuario por AJAX o se expone en una vista Vue/React/JSON, el hash cifrado de la contraseña viajará en texto plano en la red. |
| **`public function productos()` en `Categoria.php`** | `BadMethodCallException: Call to undefined method App\Models\Categoria::productos()` | Falla la instrucción `Categoria::activas()->withCount('productos')` en `TiendaController@index`, impidiendo renderizar la tienda de inicio. |

---

## 6. ❓ ESCENARIOS DE CONTINGENCIA: "¿QUÉ PASA SI SUCEDE ESTO O AQUELLO?"

### Escenario 1: ¿Qué pasa si un usuario no tiene apellido registrado?
* **Comportamiento:** En el accesor `getNombreCompletoAttribute()`, la interpolación `"{$this->nombre} {$this->apellido}"` imprime limpiamente el nombre sin generar errores ni advertencias de tipo en PHP.

### Escenario 2: ¿Qué pasa si una categoría se desactiva (`activo = 0`)?
* **Comportamiento:** El scope `scopeActivas` en `Categoria.php` la filtra de la barra de navegación del catálogo público. Sin embargo, en el panel administrativo sigue visible para que el administrador pueda reactivarla cuando lo requiera.

---

## 7. 🛠️ POSIBLES PROBLEMAS, ERRORES COMUNES Y SOLUCIONES

### Problema 1: `Call to undefined method App\Models\Usuario::isAdmin()`
* **Causa:** El método no fue declarado en `Usuario.php` o se renombró.
* **Solución:** Declarar en `Usuario.php`:
  ```php
  public function isAdmin(): bool
  {
      return (int) $this->id_rol === 1;
  }
  ```

### Problema 2: `Attempt to read property 'nombre' on null` al acceder a `$usuario->rol->nombre`
* **Causa:** El usuario en base de datos tiene `id_rol = null` o un ID que no existe en la tabla `roles`.
* **Solución:** Usar el operador null-safe de PHP 8: `$usuario->rol?->nombre ?? 'Sin Rol'`.

---

## 8. 🎓 Preguntas Frecuentes para Evaluación

1. **¿Cómo se configuró Laravel para que use la tabla `usuarios` en vez de `users`?**
   * *Respuesta:* En `config/auth.php`, dentro de `'providers' => ['users' => ['model' => App\Models\Usuario::class]]`, y dentro del modelo especificando `protected $table = 'usuarios';`.
2. **¿Por qué `Usuario.php` no usa timestamps automáticos de Laravel?**
   * *Respuesta:* Porque la tabla original en MySQL define `creado_en` y `actualizado_en` (o es gestionada manualmente). Declarar `public $timestamps = false;` o mapear `const CREATED_AT = 'creado_en';` evita que Laravel intente escribir en las columnas en inglés `created_at` y `updated_at`.
3. **¿Qué ventaja ofrece el método `isAdmin()` en el modelo frente a preguntar `$usuario->id_rol == 1` en cada vista?**
   * *Respuesta:* Encapsulamiento y mantenibilidad. Si mañana los administradores se definen por un rol con ID 5 o por múltiples roles, solo se modifica el método `isAdmin()` en `Usuario.php` y todo el sistema (controladores, middlewares y vistas) queda actualizado inmediatamente.
