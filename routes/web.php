<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPedidoController;
use App\Http\Controllers\AdminProductoController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\TiendaController;
use Illuminate\Support\Facades\Route;

// =====================================================
// RUTAS PÚBLICAS - TIENDA
// =====================================================

Route::get('/', [TiendaController::class, 'index'])->name('tienda.index');
Route::get('/catalogo', [TiendaController::class, 'index'])->name('tienda.catalogo');
Route::get('/productos/{id}', [TiendaController::class, 'show'])->name('tienda.producto');

// =====================================================
// RUTAS DEL CARRITO Y PEDIDOS (requieren autenticación)
// =====================================================

Route::middleware('auth')->group(function () {
    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::post('/carrito/actualizar', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::post('/carrito/eliminar', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::post('/carrito/descuento', [CarritoController::class, 'aplicarDescuento'])->name('carrito.descuento');

    // Checkout y Pedidos
    Route::get('/checkout', [PedidoController::class, 'checkout'])->name('checkout');
    Route::post('/pedidos/confirmar', [PedidoController::class, 'confirmar'])->name('pedidos.confirmar');
    Route::get('/pedidos/confirmacion/{id}', [PedidoController::class, 'confirmacion'])->name('pedidos.confirmacion');
    Route::get('/pedidos/historial', [PedidoController::class, 'historial'])->name('pedidos.historial');
    Route::get('/pedidos/{id}', [PedidoController::class, 'detalle'])->name('pedidos.detalle');

    // Portal Personal del Cliente ("Mi Espacio Kawaii")
    Route::get('/mi-cuenta', [ClienteController::class, 'dashboard'])->name('cliente.dashboard');
    Route::post('/cliente/perfil', [ClienteController::class, 'actualizarPerfil'])->name('cliente.perfil.actualizar');
    Route::post('/cliente/password', [ClienteController::class, 'cambiarPassword'])->name('cliente.password.actualizar');
    Route::post('/cliente/direccion', [ClienteController::class, 'guardarDireccion'])->name('cliente.direccion.guardar');
    Route::delete('/cliente/direccion/{id}', [ClienteController::class, 'eliminarDireccion'])->name('cliente.direccion.eliminar');
});

// =====================================================
// RUTAS DE AUTENTICACIÓN
// =====================================================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registro']);

// =====================================================
// RUTAS ADMINISTRADOR (requieren autenticación)
// =====================================================

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // Dashboard principal con KPIs y diagnóstico
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Herramientas de Reparación y Mantenimiento del Sistema
    Route::post('/mantenimiento/activar-stock', [AdminDashboardController::class, 'activarProductosConStock'])->name('mantenimiento.activar-stock');
    Route::post('/mantenimiento/reponer-agotados', [AdminDashboardController::class, 'reponerStockAgotados'])->name('mantenimiento.reponer-agotados');
    Route::post('/mantenimiento/limpiar-cache', [AdminDashboardController::class, 'limpiarCache'])->name('mantenimiento.limpiar-cache');
    Route::post('/mantenimiento/limpiar-carritos', [AdminDashboardController::class, 'limpiarCarritos'])->name('mantenimiento.limpiar-carritos');
    Route::post('/mantenimiento/cargar-datos-prueba', [AdminDashboardController::class, 'cargarDatosPrueba'])->name('mantenimiento.cargar-datos-prueba');
    Route::post('/productos/{id}/ajustar-stock', [AdminDashboardController::class, 'ajustarStock'])->name('productos.ajustar-stock');

    // Gestión de Productos
    Route::get('/productos', [AdminProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/crear', [AdminProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [AdminProductoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{id}/editar', [AdminProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}', [AdminProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}', [AdminProductoController::class, 'destroy'])->name('productos.destroy');

    // Gestión de Pedidos (Ventas)
    Route::get('/pedidos', [AdminPedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id}', [AdminPedidoController::class, 'show'])->name('pedidos.show');
    Route::post('/pedidos/{id}/estado', [AdminPedidoController::class, 'cambiarEstado'])->name('pedidos.estado');

    // Gestión de Usuarios y Clientes
    Route::get('/usuarios', [AdminUsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/{id}/toggle', [AdminUsuarioController::class, 'toggleActivo'])->name('usuarios.toggle');
});
