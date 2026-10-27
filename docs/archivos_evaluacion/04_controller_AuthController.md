# 🎮 Archivo: `app/Http/Controllers/AuthController.php` — Controlador de Autenticación y Sesiones

## 1. Ficha Técnica

* **Ubicación:** `app/Http/Controllers/AuthController.php`
* **Tipo:** Controlador de Autenticación personalizado (Custom Auth Controller).
* **Capa:** Controlador (C en MVC).
* **Propósito:** Gestionar el ciclo de vida de la identidad del usuario: validación de credenciales, hashing seguro con Bcrypt (`Hash::make` y `Hash::check`), prevención de fijación de sesión, redirección inteligente por rol (`isAdmin()`), asignación estricta de roles y registro de nuevos clientes.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    subgraph "Peticiones HTTP de Entrada"
        ReqLogin[POST /login] --> CtrlLogin[AuthController@login]
        ReqReg[POST /registro] --> CtrlReg[AuthController@registro]
        ReqLogout[POST /logout] --> CtrlLogout[AuthController@logout]
    end

    subgraph "Seguridad, Encriptación y Datos"
        CtrlLogin -->|Consulta por email| ModelUser[Usuario.php]
        CtrlLogin -->|Hash::check password vs password_hash| BCRYPT[Bcrypt Algorithm]
        CtrlLogin -->|Auth::login y regeneracion de sesion| SessionLaravel[Manejador de Sesiones]
        CtrlReg -->|Hash::make password| BCRYPT
        CtrlReg -->|Usuario::create con id_rol = 2 estricto| ModelUser
        ModelUser --> MySQL[(MySQL: usuarios)]
    end

    subgraph "Redirecciones por Rol"
        CtrlLogin -->|Si isAdmin es true| RedirAdmin[redirect: admin.dashboard]
        CtrlLogin -->|Si es cliente regular| RedirStore[redirect: tienda.index o intended]
        CtrlLogout -->|Invalida y regenera CSRF| RedirStore
        CtrlReg -->|Auto-login y bienvenida| RedirStore
    end
```

---

## 3. Código Fuente y Explicación Detallada de Métodos

### Método 1: `login(Request $request)` — Inicio de Sesión Seguro
```php
public function login(Request $request)
{
    // 1. Validación básica de formato
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // 2. Búsqueda del usuario en base de datos
    $usuario = Usuario::where('email', $request->email)->first();

    // 3. Verificación de existencia
    if (! $usuario) {
        return back()->withErrors(['email' => 'Credenciales incorrectas'])->withInput();
    }

    // 4. Comparación criptográfica segura de contraseñas
    if (! Hash::check($request->password, $usuario->password_hash)) {
        return back()->withErrors(['email' => 'Credenciales incorrectas'])->withInput();
    }

    // 5. Verificación de estado de cuenta
    if (! $usuario->activo) {
        return back()->withErrors(['email' => 'Tu cuenta está inactiva'])->withInput();
    }

    // 6. Autenticación en sesión con soporte para "Recordarme"
    Auth::login($usuario, $request->boolean('remember'));

    // 7. Regeneración del ID de sesión (Defensa contra Session Fixation)
    $request->session()->regenerate();

    // 8. Redirección condicional según rol
    return redirect()->intended(
        $usuario->isAdmin() ? route('admin.dashboard') : route('tienda.index')
    )->with('success', '¡Bienvenido, '.$usuario->nombre.'!');
}
```

#### Aspectos Críticos de Seguridad:
1. **Mensajes Genéricos contra Enumeración de Usuarios:** Si el usuario no existe o la contraseña no coincide, en ambos casos se retorna exactamente: `"Credenciales incorrectas"`. Esto evita que un atacante descubra qué correos electrónicos están registrados en la plataforma.
2. **`Hash::check()`:** Las contraseñas en MySQL nunca se guardan en texto plano; se almacenan en la columna `password_hash` mediante el algoritmo Bcrypt con factor de costo (salt dinámico). `Hash::check` compara el hash en tiempo constante para evitar ataques de temporización (*timing attacks*).
3. **`session()->regenerate()`:** Previene ataques de **Fijación de Sesión** (*Session Fixation*). Al autenticar al usuario, Laravel renueva completamente el identificador de cookie de sesión (`laravel_session`).
4. **`redirect()->intended()`:** Si el usuario intentaba ingresar a `/admin/productos` y fue redirigido al login, `intended()` lo devuelve a la URL a la que originalmente deseaba acceder.

---

### Método 2: `logout(Request $request)` — Cierre de Sesión Limpio
```php
public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('tienda.index')->with('success', 'Sesión cerrada correctamente');
}
```
* **`session()->invalidate()`:** Destruye todos los datos temporales almacenados en la sesión del servidor.
* **`session()->regenerateToken()`:** Vuelve a generar el token CSRF (`_token`), invalidando cualquier formulario abierto en pestañas inactivas.

---

### Método 3: `registro(Request $request)` — Alta de Clientes
```php
public function registro(Request $request)
{
    $request->validate([
        'nombre' => 'required|max:80',
        'apellido' => 'required|max:80',
        'email' => 'required|email|unique:usuarios,email',
        'password' => 'required|min:6|confirmed',
        'telefono' => 'nullable|max:30',
    ]);

    $usuario = Usuario::create([
        'id_rol' => 2, // 2 = Rol Cliente por defecto
        'nombre' => $request->nombre,
        'apellido' => $request->apellido,
        'email' => $request->email,
        'password_hash' => Hash::make($request->password),
        'telefono' => $request->telefono,
        'activo' => 1,
        'email_verificado' => 0,
    ]);

    Auth::login($usuario);

    return redirect()->route('tienda.index')->with('success', '¡Registro exitoso! Bienvenido a Junos Store');
}
```
* **Regla `confirmed`:** Valida que el campo `password` coincida exactamente con el campo `password_confirmation` en el formulario Blade.
* **Asignación estricta de Rol (`'id_rol' => 2`):** Impide que un usuario se auto-registre con privilegios administrativos (`id_rol = 1`).

---

## 4. 💥 ANÁLISIS DE IMPACTO: "¿QUÉ SUCEDE SI QUITO ESTO EN EL PROYECTO?"

| Si quitas... | Error técnico que ocurre | Impacto real en el sistema |
| :--- | :--- | :--- |
| **`Hash::make($request->password)` y guardas `$request->password`** | Ningún error sintáctico al guardar, pero el login posterior fallará al hacer `Hash::check()`. | **Vulnerabilidad de seguridad extrema:** Las contraseñas quedan almacenadas en texto plano en la base de datos MySQL. Cualquier fuga o dump expondría las credenciales privadas de todos los usuarios. |
| **`$request->session()->regenerate()`** | Ningún error visible inmediato. | **Vulnerabilidad a Session Fixation:** Un atacante puede fijar una cookie de sesión en el navegador de la víctima antes de autenticarse y secuestrar su cuenta inmediatamente después del login. |
| **`'id_rol' => 2` fijo y pones `$request->all()` en registro** | Si alguien manipula el formulario inyectando `<input name="id_rol" value="1">`, el registro lo aceptará. | **Escalamiento de privilegios:** Cualquier usuario en Internet podría registrarse y otorgarse automáticamente rol de Administrador para tomar control total de la tienda. |
| **`session()->invalidate()` en `logout()`** | La información de la sesión anterior (variables flash, carrito previo en sesión) puede persistir en la memoria del navegador. | Si dos personas comparten la misma computadora, el siguiente usuario podría acceder a datos privados del usuario anterior. |
| **`$usuario->isAdmin()` en la redirección** | Un administrador que inicie sesión será redirigido al catálogo de la tienda en vez de al dashboard de administración `/admin`. | El administrador tendría que escribir manualmente la URL `/admin` en su navegador cada vez que entre al sistema. |

---

## 5. ❓ ESCENARIOS DE CONTINGENCIA: "¿QUÉ PASA SI SUCEDE ESTO O AQUELLO?"

### Escenario 1: ¿Qué pasa si un usuario introduce una contraseña incorrecta?
* **Comportamiento:** `Hash::check` retorna `false`. La condición `if (!Hash::check(...))` redirige hacia atrás con `withErrors(['email' => 'Credenciales incorrectas'])`. El formulario se vuelve a pintar mostrando una alerta en rojo pastel y conservando el email ingresado mediante `withInput()`.

### Escenario 2: ¿Qué pasa si una cuenta fue desactivada por el administrador (`activo = 0`)?
* **Comportamiento:** La condición de la línea 66 detecta `$usuario->activo === 0` y rechaza el login con el mensaje: *"Tu cuenta está inactiva"*. Nunca se ejecuta `Auth::login()`.

### Escenario 3: ¿Qué pasa si un cliente intenta registrar un correo que ya existe en la base de datos?
* **Comportamiento:** La regla de validación `'email' => 'unique:usuarios,email'` detiene el proceso antes de tocar la tabla y arroja el error: *"El correo electrónico ya ha sido registrado"*.

---

## 6. 🛠️ POSIBLES PROBLEMAS, ERRORES COMUNES Y SOLUCIONES

### Problema 1: `Call to undefined method App\Models\Usuario::isAdmin()`
* **Causa:** El método auxiliar `isAdmin()` no existe o fue renombrado en `app/Models/Usuario.php`.
* **Solución:** Verificar que en `Usuario.php` esté definido:
  ```php
  public function isAdmin(): bool
  {
      return (int) $this->id_rol === 1;
  }
  ```

### Problema 2: El usuario inicia sesión pero al cambiar de página vuelve a pedir login
* **Causa:** La configuración de sesión en `config/session.php` o los permisos de escritura en la carpeta `storage/framework/sessions/` están fallando en Windows/Laragon.
* **Solución:** Ejecutar en terminal:
  ```powershell
  php artisan config:clear
  ```
  y verificar que `storage/framework/sessions/` tenga permisos de escritura.

---

## 7. 🎓 Preguntas Frecuentes para Evaluación

1. **¿Por qué la columna en la base de datos se llama `password_hash` y no `password`?**
   * *Respuesta:* Por la migración desde la base de datos original en PHP/MySQL. En el modelo `Usuario.php`, se especificó `public function getAuthPassword() { return $this->password_hash; }` para indicarle a Laravel que utilice esa columna como secreto de autenticación.
2. **¿Qué vulnerabilidad mitiga la regeneración de sesión en `login()`?**
   * *Respuesta:* La vulnerabilidad de **Session Fixation** (Fijación de Sesión), en la cual un atacante asigna una cookie de sesión conocida a la víctima antes de que inicie sesión para luego usurpar su cuenta.
3. **¿Cómo funciona el checkbox "Recordarme" (`remember`)?**
   * *Respuesta:* Laravel guarda una cookie segura cifrada de larga duración (`remember_web_*`) y actualiza la columna `token_recordatorio` en la tabla `usuarios`.
