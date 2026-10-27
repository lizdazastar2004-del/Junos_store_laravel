<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TiendaController — Controlador público de la tienda
 *
 * Gestiona todas las funcionalidades públicas de la tienda:
 * - Página principal con productos destacados
 * - Catálogo completo con filtros avanzados
 * - Detalle de productos individuales
 * - Filtrado AJAX de productos
 */
class TiendaController extends Controller
{
    /**
     * Página principal - Home con hero y productos destacados
     * Muestra el hero banner, estadísticas y grid de productos principales
     *
     * @return View
     */
    public function index(Request $request)
    {
        // Consulta base: productos activos con stock disponible
        $query = Producto::with(['categoria', 'subcategoria', 'imagenes'])
            ->activos()
            ->conStock();

        // Filtro de búsqueda por término
        if ($request->filled('q')) {
            $termino = $request->q;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('descripcion', 'like', "%{$termino}%");
            });
        }

        // Filtro por categoría específica
        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        // Obtener productos paginados (12 por página)
        $productos = $query->orderBy('creado_en', 'desc')->paginate(12);

        // Categorías con contador de productos para las estadísticas
        $categorias = Categoria::activas()->withCount('productos')->get();

        return view('tienda.index', compact('productos', 'categorias'));
    }

    /**
     * Catálogo completo con sidebar de filtros
     * Vista dedicada para exploración avanzada de productos con filtros por subcategoría
     *
     * @return View
     */
    public function catalogo(Request $request)
    {
        // Consulta base
        $query = Producto::with(['categoria', 'subcategoria', 'imagenes'])
            ->activos()
            ->conStock();

        // Búsqueda por término
        if ($request->filled('q')) {
            $termino = $request->q;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('descripcion', 'like', "%{$termino}%");
            });
        }

        // Filtro por múltiples subcategorías (viene como array desde checkboxes)
        if ($request->filled('subcategorias')) {
            $subcategorias = is_array($request->subcategorias)
                ? $request->subcategorias
                : explode(',', $request->subcategorias);
            $query->whereIn('id_subcategoria', $subcategorias);
        }

        // Filtro por categoría única
        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        // Obtener todos los productos (sin paginación para que el JS pueda filtrar)
        $productos = $query->orderBy('creado_en', 'desc')->get();

        // Categorías con subcategorías para el sidebar de filtros
        $categorias = Categoria::activas()
            ->with(['subcategorias' => function ($q) {
                $q->where('activo', 1);
            }])
            ->get();

        return view('tienda.catalogo', compact('productos', 'categorias'));
    }

    /**
     * Filtrar productos (Endpoint AJAX)
     * Retorna JSON con productos filtrados para actualizar el grid dinámicamente
     *
     * Acepta parámetros:
     * - subcategorias[] (array de IDs)
     * - termino (string, mínimo 3 caracteres)
     *
     * @return JsonResponse
     */
    public function filtrar(Request $request)
    {
        // Consulta base
        $query = Producto::with(['categoria', 'subcategoria', 'imagenes'])
            ->activos()
            ->conStock();

        // Filtro por subcategorías (array de IDs desde checkboxes)
        if ($request->filled('subcategorias')) {
            $subcategorias = is_array($request->subcategorias)
                ? $request->subcategorias
                : explode(',', $request->subcategorias);
            $query->whereIn('id_subcategoria', $subcategorias);
        }

        // Búsqueda por término (mínimo 3 caracteres para evitar consultas muy amplias)
        if ($request->filled('termino')) {
            $termino = trim($request->termino);
            if (mb_strlen($termino) >= 3) {
                $query->where(function ($q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('descripcion', 'like', "%{$termino}%");
                });
            }
        }

        // Ejecutar consulta
        $productos = $query->orderBy('creado_en', 'desc')->get();

        // Formatear productos para JSON con solo los datos necesarios
        $productosFormateados = $productos->map(function ($producto) {
            // Obtener imagen principal (prioridad: es_principal = 1, sino la primera)
            $imagenPrincipal = $producto->imagenes->where('es_principal', 1)->first()
                            ?? $producto->imagenes->first();

            return [
                'id' => $producto->id_producto,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'stock_disponible' => $producto->stock_actual,
                'subcategoria_nombre' => $producto->subcategoria->nombre ?? $producto->categoria->nombre,
                'imagen_principal' => $imagenPrincipal ? asset($imagenPrincipal->ruta_imagen) : null,
                'url' => route('tienda.producto', $producto->id_producto),
            ];
        });

        return response()->json($productosFormateados);
    }

    /**
     * Detalle de un producto individual
     * Muestra información completa del producto con imágenes, reseñas y productos relacionados
     *
     * @param  int  $id  ID del producto
     * @return View
     */
    public function show($id)
    {
        // Buscar producto con todas sus relaciones
        $producto = Producto::with([
            'categoria',
            'subcategoria',
            'marca',
            'imagenes',
            'resenas.usuario',
        ])
            ->findOrFail($id);

        // Verificar que el producto esté activo
        if (! $producto->activo) {
            abort(404, 'Producto no disponible');
        }

        // Obtener productos relacionados de la misma categoría
        // Excluye el producto actual y solo muestra 4
        $relacionados = Producto::with(['imagenes'])
            ->activos()
            ->conStock()
            ->where('id_categoria', $producto->id_categoria)
            ->where('id_producto', '!=', $id)
            ->limit(4)
            ->get();

        return view('tienda.detalle', compact('producto', 'relacionados'));
    }
}
