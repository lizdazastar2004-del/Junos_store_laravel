# 📋 Guía Técnica: Datos de Prueba (Seeder), Sincronización y Validación del Sistema

> **Proyecto:** Juno's Store (E-Commerce Kawaii)  
> **Framework:** Laravel 11 (PHP 8.3) & MySQL / Laragon  
> **Objetivo:** Documentar qué se implementó, para qué sirve cada componente creado y cómo sustentar ante evaluadores (SENA ADSO) el correcto funcionamiento de toda la tienda.

---

## 🎯 1. ¿Por qué se hizo esto y qué problema resuelve?

Al desarrollar o evaluar un sistema de software, **un aplicativo vacío no permite comprobar que las reglas de negocio funcionen**.

Antes de esta implementación:
1. No se podían ver las tarjetas y gráficas de ventas del Administrador porque no habían pedidos registrados.
2. No se podían probar las alertas de **stock bajo** o productos **agotados** porque faltaban artículos en diferentes estados de inventario.
3. El carrito de compras no podía validar los **códigos de descuento** (`MIAU10`, etc.) porque la tabla requerida no estaba sincronizada.
4. El cliente no tenía historial de compras ni direcciones guardadas para probar el nuevo **Portal del Cliente** (`/mi-cuenta`).

**Solución aplicada:**  
Se creó un generador automatizado de datos de prueba (**Seeder**), un botón de ejecución en 1 clic dentro del Panel Administrativo, una migración de sincronización de esquemas no destructiva y pruebas automatizadas que certifican la seguridad.

---

## 🧩 2. ¿Para qué sirve cada archivo creado o modificado?

A continuación se explica componente por componente qué hace y cuál es su función en la arquitectura del sistema:

### A. [`database/seeders/DemoDataSeeder.php`](file:///c:/laragon/www/junos-store-laravel/database/seeders/DemoDataSeeder.php) (Seeder Maestro)
* **¿Qué es?** Es una clase de siembra de base de datos (*Database Seeder*) escrita en Laravel.
* **¿Para qué sirve?**
  1. **Inserta y actualiza roles:** Asegura la existencia del Rol 1 (*Administrador*) y Rol 2 (*Cliente*).
  2. **Crea usuarios con credenciales conocidas:** Genera un administrador y dos clientes de prueba con contraseñas seguras mediante `Hash::make()`.
  3. **Genera categorías y marcas comerciales:** Crea familias como *Papelería & Escritorio*, *Peluches & Coleccionables*, *Moda & Hogar*.
  4. **Crea productos con archivos SVG físicos:** Genera archivos de imagen vectoriales reales en la carpeta `public/img/productos/{id}/` y los asocia a la tabla `producto_imagenes`.
  5. **Simula escenarios críticos de inventario:** Crea productos con stock suficiente, un producto con **stock crítico** (3 unidades vs mínimo 6) y un producto **agotado** (0 unidades) para probar los avisos del Dashboard.
  6. **Puebla cupones promocionales:** Registra los cupones `MIAU10`, `JUNO15K` y `PASTEL20` con fechas de vigencia y montos mínimos.
  7. **Genera pedidos transaccionales:** Crea pedidos en estados *Pendiente*, *En Proceso*, *Enviado* y *Entregado*, vinculando direcciones, detalles de compra y registros de pago PSE.
  8. **Registra reseñas de 5 estrellas:** Demuestra la calificación de clientes sobre los productos entregados.
* **Técnica empleada:** Utiliza `updateOrCreate()` y `firstOrCreate()` para evitar duplicar registros si se ejecuta varias veces.

---

### B. [`database/migrations/2026_10_23_000001_sync_junos_store_schema_columns.php`](file:///c:/laragon/www/junos-store-laravel/database/migrations/2026_10_23_000001_sync_junos_store_schema_columns.php) (Migración de Sincronización)
* **¿Qué es?** Es una migración de compatibilidad y sincronización de estructura de datos.
* **¿Para qué sirve?**  
  Asegura que la base de datos MySQL de Laragon y la base de datos SQLite en memoria (usada en las pruebas automatizadas) tengan exactamente las mismas columnas requeridas por los modelos Eloquent:
  - Crea la tabla `codigos_descuento` requerida por `CodigoDescuento.php` y `CarritoController.php`.
  - Agrega columnas opcionales/complementarias de forma segura (`Schema::hasColumn`): `codigo_descuento_id` y `descuento_aplicado` en carritos y pedidos, campos de notas y direcciones de entrega, y referencias de pago.
* **Principio de seguridad:** Es **no destructiva**, no borra tablas ni modifica registros existentes.

---

### C. [`app/Http/Controllers/AdminDashboardController.php`](file:///c:/laragon/www/junos-store-laravel/app/Http/Controllers/AdminDashboardController.php) (Herramienta Web)
* **¿Qué se le agregó?** El método `cargarDatosPrueba()` y la compatibilidad con conteos mayúsculas/minúsculas de pedidos.
* **¿Para qué sirve?**
  - Permite que el Administrador, desde su navegador y sin abrir la terminal, presione un botón y ejecute `Artisan::call('db:seed', ['--class' => 'DemoDataSeeder'])`.
  - Captura excepciones con `try / catch` para reportar mensajes flash de éxito o error en la interfaz.
  - Asegura que los contadores del Dashboard (*KPIs* de pedidos pendientes, en preparación, entregados y ventas totales) contabilicen pedidos sin importar si el motor de base de datos los guardó en mayúsculas (`PENDIENTE`) o minúsculas (`pendiente`).

---

### D. [`resources/views/admin/dashboard.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/dashboard.blade.php) (Interfaz Administrativa)
* **¿Qué se le agregó?** El botón interactivo **"Cargar Datos de Prueba"** dentro de la tarjeta de *Herramientas de Reparación y Mantenimiento*.
* **¿Para qué sirve?**
  - Brinda accesibilidad visual inmediata con diseño acorde a la paleta pastel del proyecto (`#7c5cbf`, `#faf5ff`).
  - Incluye confirmación preventiva JavaScript (`onsubmit="return confirm(...)"`) para evitar clics accidentales.

---

### E. [`app/Http/Controllers/AdminPedidoController.php`](file:///c:/laragon/www/junos-store-laravel/app/Http/Controllers/AdminPedidoController.php) y Vistas de Pedidos
* **¿Qué se mejoró?** La sincronización del flujo de cambio de estado de pedidos.
* **¿Para qué sirve?**
  - Mapea los valores del formulario web (`pendiente`, `confirmado`, `en_preparacion`, `enviado`, `entregado`, `cancelado`) con los valores nativos `ENUM` de MySQL (`PENDIENTE`, `PAGADO`, `EN_PROCESO`, `ENVIADO`, `ENTREGADO`, `CANCELADO`).
  - Permite que los filtros por pestañas (*Todos*, *Pendientes*, *Enviados*, etc.) y los selectores rápidos muestren la opción correcta activa mediante `strtolower()`.

---

### F. Modelos Eloquent (`Pedido.php`, `PedidoDetalle.php`, `Pago.php`, `Resena.php`)
* **¿Qué se corrigió?**
  - Se sincronizó el atributo `$fillable` con los nombres de columna reales de la base de datos (por ejemplo, `nombre_producto` en detalles de pedido, `id_pedido` en reseñas, y `numero_pedido` en pedidos).
  - Se definió explícitamente la clave primaria `$primaryKey = 'id_pedido_detalle'`.
* **¿Para qué sirve?** Evita errores de asignación masiva (*MassAssignmentException*) o violaciones de integridad (*Column doesn't have a default value*).

---

### G. [`tests/Feature/SecurityAndMiddlewareTest.php`](file:///c:/laragon/www/junos-store-laravel/tests/Feature/SecurityAndMiddlewareTest.php) (Pruebas Unitarias y de Seguridad)
* **¿Qué se agregó?** La prueba automatizada `test_only_administrator_can_trigger_demo_data_seeding()`.
* **¿Para qué sirve?**
  - Comprueba formalmente que un usuario con rol de **Cliente** que intente consumir la ruta de siembra reciba inmediatamente un código HTTP **403 Forbidden**.
  - Comprueba que solo un **Administrador** autenticado pueda disparar la carga y reciba el mensaje de éxito en sesión.
  - **Resultado actual:** **12 de 12 pruebas pasando al 100% en verde** en menos de 2 segundos.

---

## 🔑 3. Credenciales y Datos para Demostración

Usa estas cuentas preparadas para realizar las pruebas:

| Tipo de Usuario | Correo Electrónico | Contraseña | ¿Qué puedes probar con este usuario? |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@junosstore.local` | `admin123` | - Visualizar métricas y alertas de stock en `/admin`.<br>- Cambiar estado a los pedidos recibidos.<br>- Crear o editar productos y subir fotos reales.<br>- Usar los botones de reparación y caché. |
| **Cliente Demo 1** | `cliente@junosstore.local` | `cliente123` | - Entrar a `/mi-cuenta` y ver el resumen personalizado.<br>- Ver historial de pedidos y direcciones en Bogotá.<br>- Probar agregar productos al carrito y aplicar cupones. |
| **Cliente Demo 2** | `karen@junosstore.local` | `cliente123` | - Consultar pedidos en preparación con dirección en Medellín. |

---

## 🛒 4. Códigos de Descuento Habilitados

Para probar el formulario de cupones en la vista del carrito de compras:

| Cupón | Beneficio | Compra Mínima | Estado |
| :--- | :--- | :--- | :--- |
| **`MIAU10`** | 10% de descuento porcentual | $20.000 COP | Activo (Vigente por 60 días) |
| **`JUNO15K`** | $15.000 COP de descuento fijo | $70.000 COP | Activo (Vigente por 60 días) |
| **`PASTEL20`** | 20% de descuento porcentual | $90.000 COP | Activo (Vigente por 60 días) |

---

## 📊 5. Escenarios de Prueba Listos para Sustentar ante el Evaluador

| Escenario a Demostrar | Pasos a Seguir | Resultado Esperado en Pantalla |
| :--- | :--- | :--- |
| **1. Alerta de Stock Bajo** | 1. Iniciar sesión como `admin@junosstore.local`.<br>2. Ver la tabla "⚠️ Productos con Alerta de Stock". | El producto *Peluche Gatito Mochi* aparece con **3 unidades** en rojo/amarillo porque su mínimo configurado es 6. |
| **2. Reabastecimiento de Agotados** | 1. En el Dashboard, ubicar el botón "Reponer Agotados".<br>2. Hacer clic. | La *Taza Cerámica Gatito* que tenía stock 0 pasa automáticamente a tener existencias de emergencia. |
| **3. Despacho de Pedidos** | 1. Ir a `/admin/pedidos`.<br>2. En el pedido pendiente, cambiar el selector a "Enviado". | El badge cambia a celeste con icono de camión 🚚 y se actualiza en tiempo real en la base de datos. |
| **4. Portal Exclusivo del Cliente** | 1. Iniciar sesión como `cliente@junosstore.local`.<br>2. El sistema redirige a `/mi-cuenta`. | Se despliegan tarjetas kawaii con sus compras, su carrito activo y su libreta de direcciones guardadas. |
| **5. Seguridad y Roles (RBAC)** | 1. Estando con la sesión de cliente, escribir manualmente `/admin` en la URL. | El Middleware intercepta la petición y muestra la pantalla personalizada **403 Acceso Denegado**. |

---

## 🗣️ 6. Preguntas Típicas del Evaluador / Instructor y Cómo Responder

### Pregunta 1: "¿Por qué crearon un Seeder en lugar de meter los datos manualmente por phpMyAdmin?"
> **Respuesta recomendada:**  
> *"Porque seguir las buenas prácticas de Laravel y el principio de reproducibilidad exige que cualquier desarrollador o evaluador pueda clonar el repositorio, ejecutar `php artisan db:seed` y tener un entorno completamente funcional en segundos, sin depender de volcados SQL externos propensos a fallos de codificación o IDs huérfanos."*

### Pregunta 2: "¿Cómo evitan que la carga de datos de prueba borre la información que ya existía?"
> **Respuesta recomendada:**  
> *"El seeder utiliza métodos idempotentes de Eloquent como `updateOrCreate()` y `firstOrCreate()`. Esto significa que si el registro con ese SKU, correo o código ya existe, únicamente actualiza sus campos sin duplicar ni destruir la información previa."*

### Pregunta 3: "¿Cómo se protegió la ruta de sembrado para que no sea explotada?"
> **Respuesta recomendada:**  
> *"La ruta `admin/mantenimiento/cargar-datos-prueba` está encapsulada dentro del grupo de rutas protegido por dos capas de Middleware: `auth` (garantiza sesión activa) y `admin` (`AdminMiddleware`, que verifica que `id_rol === 1`). Si un cliente o usuario no autenticado intenta consumirla vía POST, el sistema aborta con error HTTP 403. Esto está validado mediante una prueba unitaria en `SecurityAndMiddlewareTest`."*

---

## 🛠️ 7. Comandos de Mantenimiento Rápido

Si en algún momento necesitas restablecer o verificar el proyecto:

```bash
# 1. Ejecutar las migraciones pendientes
php artisan migrate

# 2. Poblar o refrescar los datos de prueba
php artisan db:seed --class=DemoDataSeeder

# 3. Limpiar cachés de vistas, rutas y configuración
php artisan optimize:clear

# 4. Correr la batería de pruebas automatizadas
php artisan test --compact

# 5. Formatear el código con el estándar de Laravel Pint
vendor/bin/pint --format agent
```
