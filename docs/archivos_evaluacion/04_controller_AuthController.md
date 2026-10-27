# 🎮 Archivo: `app/Http/Controllers/AuthController.php` — Controlador de Autenticación y Sesiones

## 1. Ficha Técnica

* **Ubicación:** `app/Http/Controllers/AuthController.php`
* **Tipo:** Controlador de Autenticación personalizado (Custom Auth Controller).
* **Capa:** Controlador (C en MVC).
* **Propósito:** Gestionar el ciclo de vida de la identidad del usuario: validación de credenciales, hashing seguro con Bcrypt (`Hash::make` y `Hash::check`), prevención de fijación de sesión, redirección inteligente por rol (`isAdmin()`) y registro de nuevos clientes.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    subgraph "Peticiones HTTP"
        ReqLogin[POST /login] --> CtrlLogin[AuthController@login]
        ReqReg[POST /registro] --> CtrlReg[AuthController@registro]
        ReqLogout[POST /logout] --> CtrlLogout[AuthController@logout]
    end

    subgraph "Seguridad y Datos"
        CtrlLogin -->|Consulta por email| ModelUser[Usuario.php]
        CtrlLogin -->|Hash::check password vs password_hash| BCRYPT[Bcrypt Algorithm]
        CtrlLogin -->|Auth::login e invalidación previa| SessionLaravel[Manejador de Sesiones]
        CtrlReg -->|Hash::make password| BCRYPT
        CtrlReg -->|Usuario::create rol: 2 Cliente| ModelUser
    end

    subgraph "Redirecciones por Rol"
        CtrlLogin -->|Si isAdmin es true| RedirAdmin[redirect: admin.dashboard]
        CtrlLogin -->|Si es cliente regular| RedirStore[redirect: tienda.index]
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

## 4. Preguntas Frecuentes para Evaluación

1. **¿Por qué la columna en la base de datos se llama `password_hash` y no `password`?**
   * *Respuesta:* Por la migración desde la base de datos original en PHP/MySQL. En el modelo `Usuario.php`, se especificó `public function getAuthPassword() { return $this->password_hash; }` para indicarle a Laravel que utilice esa columna como secreto de autenticación.
2. **¿Qué vulnerabilidad mitiga la regeneración de sesión en `login()`?**
   * *Respuesta:* La vulnerabilidad de **Session Fixation** (Fijación de Sesión), en la cual un atacante asigna una cookie de sesión conocida a la víctima antes de que inicie sesión para luego usurpar su cuenta.
3. **¿Cómo funciona el checkbox "Recordarme" (`remember`)?**
   * *Respuesta:* Laravel guarda una cookie segura cifrada de larga duración (`remember_web_*`) y actualiza la columna `token_recordatorio` en la tabla `usuarios`.
