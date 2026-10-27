<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Muestra el panel de control principal con métricas completas,
     * diagnóstico de salud del proyecto y acciones de mantenimiento.
     */
    public function index(): View
    {
        // Métricas de Productos
        $totalProductos = Producto::count();
        $productosActivos = Producto::where('activo', 1)->count();
        $productosAgotados = Producto::where('stock_actual', '<=', 0)->count();
        $productosStockBajo = Producto::where('stock_actual', '>', 0)
            ->whereRaw('stock_actual <= stock_minimo')
            ->count();
        $totalCategorias = Categoria::count();

        // Métricas de Pedidos y Ventas
        $totalPedidos = Pedido::count();
        $pedidosPendientes = Pedido::whereIn('estado', ['pendiente', 'PENDIENTE'])->count();
        $pedidosEnProceso = Pedido::whereIn('estado', ['confirmado', 'en_preparacion', 'enviado', 'EN_PROCESO', 'ENVIADO', 'PAGADO'])->count();
        $pedidosEntregados = Pedido::whereIn('estado', ['entregado', 'ENTREGADO'])->count();
        $ventasTotales = Pedido::whereNotIn('estado', ['cancelado', 'CANCELADO'])->sum('total') ?? 0;

        // Métricas de Usuarios y Carritos
        $totalClientes = Usuario::where('id_rol', '!=', 1)->count();
        $totalAdmins = Usuario::where('id_rol', 1)->count();
        $carritosActivos = Carrito::where('estado', 'ACTIVO')->orWhere('estado', 'activo')->count();

        // Listas para visualización interactiva en el Dashboard
        $ultimosPedidos = Pedido::with(['usuario', 'detalles.producto'])
            ->orderBy('id_pedido', 'desc')
            ->limit(6)
            ->get();

        $productosAlertaStock = Producto::with(['categoria', 'imagenes'])
            ->whereRaw('stock_actual <= stock_minimo')
            ->orderBy('stock_actual', 'asc')
            ->limit(6)
            ->get();

        // Diagnóstico de Salud del Sistema
        $versionMotor = '8.x';
        try {
            $versionMotor = DB::select('SELECT VERSION() as v')[0]->v ?? '8.x';
        } catch (\Throwable) {
            $versionMotor = config('database.default');
        }

        $diagnostico = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_connection' => 'OK ('.config('database.default').' '.$versionMotor.')',
            'debug_mode' => config('app.debug') ? 'Activo (Desarrollo)' : 'Desactivado (Producción)',
            'storage_ok' => file_exists(public_path('img')),
            'productos_inactivos_con_stock' => Producto::where('stock_actual', '>', 0)->where('activo', 0)->count(),
        ];

        return view('admin.dashboard', compact(
            'totalProductos',
            'productosActivos',
            'productosAgotados',
            'productosStockBajo',
            'totalCategorias',
            'totalPedidos',
            'pedidosPendientes',
            'pedidosEnProceso',
            'pedidosEntregados',
            'ventasTotales',
            'totalClientes',
            'totalAdmins',
            'carritosActivos',
            'ultimosPedidos',
            'productosAlertaStock',
            'diagnostico'
        ));
    }

    /**
     * Herramienta 1: Activar todos los productos que tienen stock actual > 0
     * pero estaban marcados como inactivos (arreglo tras migraciones).
     */
    public function activarProductosConStock(): RedirectResponse
    {
        $afectados = Producto::where('stock_actual', '>', 0)
            ->where('activo', 0)
            ->update(['activo' => 1]);

        return redirect()->route('admin.dashboard')->with(
            'success',
            "¡Catálogo reparado con éxito! Se activaron {$afectados} productos que tenían existencias."
        );
    }

    /**
     * Herramienta 2: Reabastecer stock de emergencia para productos agotados.
     */
    public function reponerStockAgotados(): RedirectResponse
    {
        $afectados = DB::statement('UPDATE productos SET stock_actual = GREATEST(stock_minimo, 5) WHERE stock_actual <= 0');

        return redirect()->route('admin.dashboard')->with(
            'success',
            '¡Stock de emergencia asignado! Los productos agotados recibieron unidades para habilitar ventas.'
        );
    }

    /**
     * Herramienta 3: Ajuste rápido de stock en 1 clic para un producto específico.
     */
    public function ajustarStock(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'cantidad' => 'required|integer',
        ]);

        $producto = Producto::findOrFail($id);
        $nuevoStock = max(0, $producto->stock_actual + (int) $request->input('cantidad'));
        $producto->stock_actual = $nuevoStock;

        // Si ahora tiene stock y estaba inactivo, lo activamos
        if ($nuevoStock > 0 && ! $producto->activo) {
            $producto->activo = 1;
        }

        $producto->save();

        return redirect()->back()->with(
            'success',
            "Stock actualizado para '{$producto->nombre}'. Nuevo stock: {$nuevoStock} unidades."
        );
    }

    /**
     * Herramienta 4: Limpiar toda la caché del sistema (vistas, configuración, rutas).
     */
    public function limpiarCache(): RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');

            return redirect()->route('admin.dashboard')->with(
                'success',
                '¡Caché del sistema vaciada correctamente! Vistas, rutas y configuraciones se han refrescado.'
            );
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard')->with(
                'error',
                'Ocurrió un error al limpiar la caché: '.$e->getMessage()
            );
        }
    }

    /**
     * Herramienta 5: Limpieza de carritos abandonados antiguos.
     */
    public function limpiarCarritos(): RedirectResponse
    {
        $afectados = Carrito::where('estado', 'ACTIVO')
            ->where('actualizado_en', '<', now()->subDays(5))
            ->update(['estado' => 'ABANDONADO']);

        return redirect()->route('admin.dashboard')->with(
            'success',
            "Se marcaron {$afectados} carritos inactivos como abandonados para mantener la base de datos optimizada."
        );
    }

    /**
     * Herramienta 6: Generar / Sincronizar datos de prueba (DemoDataSeeder)
     * en 1 clic directamente desde el panel de administración.
     */
    public function cargarDatosPrueba(): RedirectResponse
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DemoDataSeeder',
                '--force' => true,
            ]);

            return redirect()->route('admin.dashboard')->with(
                'success',
                '¡Datos de prueba cargados exitosamente! Se poblaron usuarios de prueba, catálogo kawaii con imágenes SVG, cupones promocionales y pedidos en todos los estados para evaluación.'
            );
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard')->with(
                'error',
                'Error al generar datos de prueba: '.$e->getMessage()
            );
        }
    }
}
