# 🎀 Archivo: `app/Http/Controllers/ClienteController.php` — Portal "Mi Espacio Kawaii" del Cliente

## 1. Ficha Técnica

* **Ubicación:** `app/Http/Controllers/ClienteController.php`
* **Tipo:** Clase Controladora de Experiencia de Usuario (Portal del Comprador).
* **Capa:** Controlador (C en MVC).
* **Propósito:** Ofrecer un panel personal exclusivo para el rol de Cliente (`id_rol = 2`), brindando una experiencia estética, acogedora y kawaii, opuesta al enfoque técnico y cuantitativo del panel de administración. Permite consultar el historial de pedidos con sus fotos, reanudar carritos activos, gestionar libretas de direcciones y actualizar credenciales de seguridad.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    subgraph "Entrada HTTP (middleware: auth)"
        RouteDash[GET /mi-cuenta] --> CliDash[ClienteController@dashboard]
        RoutePerfil[POST /cliente/perfil] --> CliPerfil[ClienteController@actualizarPerfil]
        RoutePass[POST /cliente/password] --> CliPass[ClienteController@cambiarPassword]
        RouteDirStore[POST /cliente/direccion] --> CliDirStore[ClienteController@guardarDireccion]
        RouteDirDel[DELETE /cliente/direccion/{id}] --> CliDirDel[ClienteController@eliminarDireccion]
    end

    subgraph "Modelos y Relaciones Consultadas"
        CliDash -->|Auth::user| ModelUser[Usuario.php]
        CliDash -->|Pedido::with detalles.producto.imagenes| ModelPed[Pedido.php]
        CliDash -->|Direccion::where id_usuario| ModelDir[Direccion.php]
        CliDash -->|Carrito::where id_usuario estado activo| ModelCart[Carrito.php]
        CliPerfil --> ModelUser
        CliPass -->|Hash::check password_actual + Hash::make| ModelUser
        CliDirStore --> ModelDir
        CliDirDel --> ModelDir
    end

    subgraph "Salida / Presentación"
        CliDash --> ViewDash[resources/views/cliente/dashboard.blade.php]
        ViewDash --> LayoutApp[layouts/app.blade.php + CSS Kawaii]
    end
```

---

## 3. Código Fuente y Explicación Detallada de Métodos

### Método 1: `dashboard()` — Resumen de Comprador y Pedidos
```php
public function dashboard(): View
{
    /** @var Usuario $usuario */
    $usuario = Auth::user();

    // 1. Obtener los últimos pedidos del cliente con sus detalles y productos asociados
    $pedidos = Pedido::with(['detalles.producto.imagenes', 'pago.metodoPago'])
        ->where('id_usuario', $usuario->id_usuario)
        ->orderBy('id_pedido', 'desc')
        ->get();

    // 2. Métricas rápidas para las tarjetas de bienvenida del cliente
    $totalPedidos = $pedidos->count();
    $pedidosActivos = $pedidos->whereIn('estado', ['pendiente', 'confirmado', 'en_preparacion', 'enviado'])->count();
    $pedidosEntregados = $pedidos->whereIn('estado', ['entregado'])->count();
    $totalInvertido = $pedidos->whereNotIn('estado', ['cancelado'])->sum('total');

    // 3. Direcciones de despacho guardadas por el cliente
    $direcciones = Direccion::where('id_usuario', $usuario->id_usuario)
        ->where('activo', 1)
        ->orderBy('id_direccion', 'desc')
        ->get();

    // 4. Carrito de compras activo en este momento (para reanudar)
    $carrito = Carrito::where('id_usuario', $usuario->id_usuario)
        ->whereIn('estado', ['activo', 'ACTIVO'])
        ->with(['detalles.producto.imagenes'])
        ->first();

    // 5. Saludo personalizado según la hora
    $hora = (int) date('H');
    $saludo = ($hora >= 5 && $hora < 12) ? '¡Buenos días!' : (($hora >= 12 && $hora < 19) ? '¡Buenas tardes!' : '¡Buenas noches!');

    return view('cliente.dashboard', compact(
        'usuario', 'pedidos', 'totalPedidos', 'pedidosActivos',
        'pedidosEntregados', 'totalInvertido', 'direcciones', 'carrito', 'saludo'
    ));
}
```

---

### Método 2: `cambiarPassword(Request $request)` — Cambio Seguro de Contraseña
```php
public function cambiarPassword(Request $request): RedirectResponse
{
    $usuario = Auth::user();

    $request->validate([
        'password_actual' => 'required',
        'password_nuevo' => 'required|min:6|confirmed',
    ]);

    // 1. Verificar criptográficamente que la contraseña actual sea la correcta
    if (! Hash::check($request->password_actual, $usuario->password_hash)) {
        return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta']);
    }

    // 2. Hashear y persistir la nueva contraseña
    $usuario->password_hash = Hash::make($request->password_nuevo);
    $usuario->save();

    return back()->with('success_password', '¡Contraseña actualizada con éxito! 🎀');
}
```

---

### Método 3: `eliminarDireccion($id)` — Borrado Seguro de Direcciones
```php
public function eliminarDireccion($id): RedirectResponse
{
    // Verificación de pertenencia estricta para evitar ataques IDOR
    $direccion = Direccion::where('id_direccion', $id)
        ->where('id_usuario', Auth::id())
        ->firstOrFail();

    $direccion->activo = 0; // Borrado lógico
    $direccion->save();

    return back()->with('success_direccion', 'Dirección eliminada correctamente.');
}
```

---

## 4. 💥 ANÁLISIS DE IMPACTO: "¿QUÉ SUCEDE SI QUITO ESTO EN EL PROYECTO?"

| Si quitas... | Error técnico que ocurre | Impacto real en el sistema |
| :--- | :--- | :--- |
| **`where('id_usuario', $usuario->id_usuario)` en la consulta de pedidos** | Ningún error de código, pero traerá todos los pedidos de la tabla. | **Fuga masiva de privacidad:** Cada cliente al entrar a `/mi-cuenta` vería las compras, montos gastados y direcciones de entrega de todos los demás clientes de la tienda. |
| **`Hash::check($request->password_actual, ...)` en cambio de contraseña** | Ningún error sintáctico. | **Fallo crítico de seguridad (Account Takeover):** Si un cliente deja su sesión abierta en un cibercafé o biblioteca, cualquier persona podría cambiar su contraseña directamente sin necesidad de conocer la clave actual. |
| **`where('id_usuario', Auth::id())` en `eliminarDireccion`** | Vulnerabilidad **IDOR**: `Direccion::findOrFail($id)->delete()`. | Un usuario malicioso podría enviar peticiones DELETE con números secuenciales (`/cliente/direccion/1`, `/cliente/direccion/2`) y borrar las libretas de direcciones de otros usuarios. |
| **El Eager Loading `with(['detalles.producto.imagenes'])`** | Lentitud extrema y decenas de queries en el log de MySQL. | Si el cliente tiene 10 pedidos con 3 productos cada uno, Laravel ejecutará más de 40 consultas SQL individuales solo para mostrar las miniaturas en la vista. |

---

## 5. ❓ ESCENARIOS DE CONTINGENCIA: "¿QUÉ PASA SI SUCEDE ESTO O AQUELLO?"

### Escenario 1: ¿Qué pasa si el cliente no tiene ningún pedido todavía?
* **Comportamiento:** `$pedidos` es una colección vacía. En la vista Blade, la sección de pedidos despliega un contenedor decorativo con una ilustración de bienvenida: *"Aún no tienes pedidos registrados. ¡Explora nuestro catálogo kawaii y consiéntete!"*, junto con un botón directo a `/catalogo`.

### Escenario 2: ¿Qué pasa si el cliente ingresa una contraseña nueva que no coincide con la confirmación?
* **Comportamiento:** La regla `confirmed` en `'password_nuevo' => 'required|min:6|confirmed'` detecta que `password_nuevo` y `password_nuevo_confirmation` difieren. Laravel devuelve el error en sesión flash y la contraseña en base de datos no sufre ninguna alteración.

---

## 6. 🛠️ POSIBLES PROBLEMAS, ERRORES COMUNES Y SOLUCIONES

### Problema 1: `ModelNotFoundException` al intentar borrar una dirección
* **Causa:** El usuario intentó borrar un ID de dirección que no existe o que pertenece a otro cliente (bloqueado por `firstOrFail()`).
* **Solución:** Es el comportamiento de seguridad esperado. Laravel genera una respuesta 404 controlada.

### Problema 2: `Undefined variable $saludo`
* **Causa:** Se omitió `'saludo'` dentro del `compact(...)` en `dashboard()`.
* **Solución:** Verificar que `compact('usuario', 'pedidos', ..., 'saludo')` incluya todas las variables inyectadas a la plantilla Blade.

---

## 7. 🎓 Preguntas Frecuentes para Evaluación

1. **¿Por qué existe este portal si ya existe un panel de administración?**
   * *Respuesta:* Por el principio de **Separación de Responsabilidades y Diseño Centrado en el Usuario (UX)**. Un cliente no debe ver herramientas de gestión ni datos globales de la empresa. Necesita un espacio acogedor, íntimo y simple para gestionar sus compras, mientras que el administrador requiere KPIs globales de facturación y control de inventario.
2. **¿Cómo se previene el ataque IDOR en las rutas del cliente?**
   * *Respuesta:* Vinculando siempre cada consulta a la clave foránea del usuario en sesión (`where('id_usuario', Auth::id())`). De este modo, aunque el usuario manipule los parámetros de la URL, el sistema solo operará sobre registros que le pertenezcan legalmente.
