# 07. Guía de Instalación y Ejecución del Proyecto — Juno's Store

## 1. Requisitos Previos del Sistema
Antes de configurar el entorno de desarrollo, asegúrate de contar con los siguientes programas instalados en el equipo:

* **Servidor Local:** Laragon (recomendado para Windows), XAMPP o WampServer con:
  * PHP `>= 8.2` (con extensiones activas: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`).
  * MySQL `>= 8.0` o MariaDB `>= 10.4`.
* **Gestor de Paquetes PHP:** Composer `>= 2.2`.
* **Entorno Node.js / NPM:** Node.js `>= 18.x` y NPM `>= 9.x` (para Vite y empaquetado de assets).
* **Control de Versiones:** Git.

---

## 2. Paso a Paso de Instalación y Configuración

### Paso 1: Clonar o Ubicar el Proyecto
Coloca la carpeta del proyecto en el directorio raíz de tu servidor local (en Laragon suele ser `C:\laragon\www\junos-store-laravel` o `C:\laragon\www\prototipo`):

```bash
cd C:\laragon\www\junos-store-laravel
```

---

### Paso 2: Instalación de Dependencias de PHP con Composer
Ejecuta la instalación de paquetes definidos en `composer.json`:

```bash
composer install
```
*Si estás en un entorno de desarrollo y surgen advertencias de compatibilidad de paquetes menores, puedes ejecutar:*
```bash
composer install --ignore-platform-reqs
```

---

### Paso 3: Configuración del Archivo de Entorno (`.env`)
1. Duplica el archivo de ejemplo para crear tu archivo `.env` local:
   ```bash
   cp .env.example .env
   ```
   *(En PowerShell de Windows puedes usar: `copy .env.example .env`)*

2. Genera la clave de cifrado única de la aplicación:
   ```bash
   php artisan key:generate
   ```

3. Abre el archivo `.env` en tu editor de código y configura los parámetros de conexión a MySQL:
   ```env
   APP_NAME="Juno's Store"
   APP_ENV=local
   APP_KEY=base64:... (generada automáticamente)
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=junos_store
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   > [!NOTE]
   > En Laragon por defecto el usuario es `root` y la contraseña está vacía (`DB_PASSWORD=`).

---

### Paso 4: Preparación de la Base de Datos Existente

> [!CAUTION]
> **ADVERTENCIA DE INTEGRIDAD:** ¡NO ejecutes `php artisan migrate:fresh` a la ligera! El comando `migrate:fresh` BORRA todas las tablas de la base de datos sin confirmación.

Para preparar la base de datos `junos_store` existen dos alternativas según el caso:

#### Opción A: Cargar el Esquema SQL Original con Datos de Prueba
Si estás iniciando con una base de datos vacía, importa el archivo SQL de Juno's Store desde MySQL o HeidiSQL:
```sql
CREATE DATABASE IF NOT EXISTS junos_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE junos_store;
SOURCE C:/laragon/www/junos-store/sql/schema.sql;
SOURCE C:/laragon/www/junos-store/sql/seed.sql;
```

#### Opción B: Ejecutar Migraciones de Laravel (Si se van a crear tablas nuevas)
```bash
php artisan migrate
```

---

### Paso 5: Instalación y Compilación de Dependencias Frontend
Instala los paquetes de Node y compila los recursos con Vite:

```bash
npm install
npm run build
```
Para desarrollo activo con recarga en vivo de estilos CSS y scripts:
```bash
npm run dev
```

---

### Paso 6: Crear el Enlace Simbólico de Almacenamiento (Storage)
Para que las imágenes subidas por los usuarios sean accesibles públicamente:
```bash
php artisan storage:link
```

---

## 3. Comandos Útiles para Ejecución y Diagnóstico

### Iniciar el Servidor de Desarrollo
```bash
php artisan serve
```
La aplicación quedará disponible en el navegador en la dirección: **`http://127.0.0.1:8000`**

### Consultar el Listado Completo de Rutas Registradas
Permite verificar qué controlador y qué método responde a cada URL:
```bash
php artisan route:list
```
Para filtrar únicamente las rutas del administrador:
```bash
php artisan route:list --path=admin
```

---

## 4. Comandos de Limpieza de Caché (Mantenimiento)
Si realizas cambios en rutas, archivos `.env` o plantillas Blade y no se reflejan en el navegador, ejecuta este combo de limpieza:

```bash
# Limpiar caché de configuración (.env)
php artisan config:clear

# Limpiar caché de rutas (web.php)
php artisan route:clear

# Limpiar vistas Blade compiladas
php artisan view:clear

# Limpiar caché general de la aplicación
php artisan cache:clear

# Comando integral que optimiza y limpia todo a la vez
php artisan optimize:clear
```

---

## 5. Diagnóstico de Errores Frecuentes y Soluciones

### Error 1: `SQLSTATE[HY000] [1049] Unknown database 'junos_store'`
* **Causa:** La base de datos no ha sido creada en el motor MySQL.
* **Solución:** Abre HeidiSQL o phpMyAdmin y ejecuta `CREATE DATABASE junos_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`.

### Error 2: `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`
* **Causa:** El servicio MySQL de Laragon o XAMPP está apagado.
* **Solución:** Inicia el servidor MySQL en el panel de Laragon (botón "Start All").

### Error 3: `Vite manifest not found at: .../public/build/manifest.json`
* **Causa:** No se han compilado los archivos frontend o no se está ejecutando el servidor de Vite.
* **Solución:** Ejecuta en la terminal `npm run build` o mantén en segundo plano `npm run dev`.

### Error 4: `419 Page Expired` al Enviar un Formulario
* **Causa:** Falta el token de protección CSRF en el formulario HTML.
* **Solución:** Asegúrate de incluir la directiva `@csrf` inmediatamente después de abrir la etiqueta `<form>` en tu archivo Blade.

### Error 5: `The stream or file ".../storage/logs/laravel.log" could not be opened: failed to open stream: Permission denied`
* **Causa:** Permisos de escritura bloqueados en la carpeta `storage/`.
* **Solución:** En Windows, asegúrate de que la carpeta `storage` y `bootstrap/cache` no tengan el atributo de sólo lectura. En Linux: `chmod -R 775 storage bootstrap/cache`.
