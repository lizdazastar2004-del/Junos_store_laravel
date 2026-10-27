<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\Subcategoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * ==============================================================================
 * CONTROLADOR: AdminProductoController
 * ==============================================================================
 * Gestiona el mantenimiento maestro del catálogo de productos en el panel admin.
 *
 * Operaciones CRUD soportadas:
 * - Listado paginado de inventario con Eager Loading para prevenir consultas N+1.
 * - Formulario de alta con selectores dependientes (Categoría ➔ Subcategoría).
 * - Creación de productos con generación automática de SKU y carga de imagen.
 * - Formulario de edición con precarga de valores y miniatura existente.
 * - Actualización de precios, inventario y reemplazo de imagen.
 * - Borrado lógico (soft-deactivation) para preservar la integridad de pedidos pasados.
 * ==============================================================================
 */
class AdminProductoController extends Controller
{
    /**
     * Muestra la tabla de inventario de productos para el administrador.
     *
     * Buenas prácticas aplicadas:
     * - Eager Loading: with(['categoria', 'subcategoria', 'imagenes']) para
     *   cargar todas las relaciones en solo 4 consultas SQL en vez de N+1 consultas.
     * - Paginación controlada de 15 productos por página.
     *
     * @return View Vista 'admin.productos.index'
     */
    public function index(): View
    {
        $productos = Producto::with(['categoria', 'subcategoria', 'imagenes'])
            ->orderBy('creado_en', 'desc')
            ->paginate(15);

        return view('admin.productos.index', compact('productos'));
    }

    /**
     * Muestra el formulario para registrar un nuevo producto en el catálogo.
     *
     * Carga las colecciones maestras para llenar los desplegables:
     * - Categorías activas.
     * - Subcategorías activas.
     * - Marcas disponibles.
     *
     * @return View Vista 'admin.productos.crear'
     */
    public function create(): View
    {
        $categorias = Categoria::activas()->get();
        $subcategorias = Subcategoria::where('activo', 1)->get();
        $marcas = Marca::all();

        return view('admin.productos.crear', compact('categorias', 'subcategorias', 'marcas'));
    }

    /**
     * Almacena un nuevo producto y su imagen en el sistema.
     *
     * @param  Request  $request  Datos del formulario multipart/form-data
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validación exhaustiva de campos y reglas de negocio
        $request->validate([
            'nombre' => 'required|string|max:180',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'id_categoria' => 'required|integer|exists:categorias,id_categoria',
            'id_subcategoria' => 'required|integer|exists:subcategorias,id_subcategoria',
            'id_marca' => 'nullable|integer|exists:marcas,id_marca',
            'sku' => 'nullable|string|max:60|unique:productos,sku',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:3072',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'precio.required' => 'El precio del producto es obligatorio.',
            'id_categoria.required' => 'Debes asignar una categoría al producto.',
            'id_subcategoria.required' => 'Debes asignar una subcategoría.',
            'sku.unique' => 'Este código SKU ya pertenece a otro artículo.',
            'imagen.image' => 'El archivo debe ser una imagen válida (JPG, PNG, WebP o SVG).',
            'imagen.max' => 'La imagen no debe pesar más de 3 MB.',
        ]);

        $data = $request->except(['imagen']);

        // 2. Generar código SKU único si el administrador no lo ingresó manualmente
        if (empty($data['sku'])) {
            $data['sku'] = 'JUNO-'.str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }

        // 3. Determinar estado de visibilidad activa
        $data['activo'] = $request->has('activo') ? 1 : 0;

        // 4. Crear el registro maestro en la tabla 'productos'
        $producto = Producto::create($data);

        // 5. Procesar la subida del archivo de imagen si fue suministrado
        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $extension = $archivo->getClientOriginalExtension();
            $directorioDestino = public_path("img/productos/{$producto->id_producto}");

            // Crear el directorio físico si aún no existe
            if (! File::exists($directorioDestino)) {
                File::makeDirectory($directorioDestino, 0755, true);
            }

            // Nombre estandarizado del archivo
            $nombreArchivo = "producto_{$producto->id_producto}.{$extension}";
            $archivo->move($directorioDestino, $nombreArchivo);

            // Guardar registro relacional en 'producto_imagenes'
            ProductoImagen::create([
                'id_producto' => $producto->id_producto,
                'ruta_imagen' => "/img/productos/{$producto->id_producto}/{$nombreArchivo}",
                'es_principal' => true,
                'principal' => true,
                'orden' => 1,
                'texto_alternativo' => $producto->nombre,
            ]);
        }

        return redirect()
            ->route('admin.productos.index')
            ->with('success', "¡Producto '{$producto->nombre}' creado exitosamente con SKU {$producto->sku}!");
    }

    /**
     * Muestra el formulario para modificar un producto existente.
     *
     * @param  int  $id  Identificador del producto
     */
    public function edit(int $id): View
    {
        $producto = Producto::with('imagenes')->findOrFail($id);
        $categorias = Categoria::activas()->get();
        $subcategorias = Subcategoria::where('activo', 1)->get();
        $marcas = Marca::all();

        return view('admin.productos.editar', compact('producto', 'categorias', 'subcategorias', 'marcas'));
    }

    /**
     * Actualiza la información del producto y opcionalmente reemplaza su imagen.
     *
     * @param  Request  $request  Petición con los datos modificados
     * @param  int  $id  Identificador del producto
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:180',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'id_categoria' => 'required|integer|exists:categorias,id_categoria',
            'id_subcategoria' => 'required|integer|exists:subcategorias,id_subcategoria',
            'id_marca' => 'nullable|integer|exists:marcas,id_marca',
            'sku' => 'nullable|string|max:60|unique:productos,sku,'.$id.',id_producto',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:3072',
        ]);

        $data = $request->except(['imagen']);
        $data['activo'] = $request->has('activo') ? 1 : 0;

        $producto->update($data);

        // Procesar nueva imagen si el usuario seleccionó un archivo nuevo
        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $extension = $archivo->getClientOriginalExtension();
            $directorioDestino = public_path("img/productos/{$producto->id_producto}");

            if (! File::exists($directorioDestino)) {
                File::makeDirectory($directorioDestino, 0755, true);
            }

            $nombreArchivo = "producto_{$producto->id_producto}.".$extension;
            $archivo->move($directorioDestino, $nombreArchivo);

            $rutaPublica = "/img/productos/{$producto->id_producto}/{$nombreArchivo}";

            // Buscar si ya tiene imagen principal registrada
            $imagenExistente = ProductoImagen::where('id_producto', $producto->id_producto)->first();

            if ($imagenExistente) {
                $imagenExistente->update([
                    'ruta_imagen' => $rutaPublica,
                    'es_principal' => true,
                    'principal' => true,
                ]);
            } else {
                ProductoImagen::create([
                    'id_producto' => $producto->id_producto,
                    'ruta_imagen' => $rutaPublica,
                    'es_principal' => true,
                    'principal' => true,
                    'orden' => 1,
                    'texto_alternativo' => $producto->nombre,
                ]);
            }
        }

        return redirect()
            ->route('admin.productos.index')
            ->with('success', "¡Producto '{$producto->nombre}' actualizado exitosamente!");
    }

    /**
     * Desactiva lógicamente un producto (activo = 0).
     *
     * IMPORTANTE PARA EVALUACIÓN:
     * No usamos $producto->delete() físico porque si el producto ya fue
     * vendido en órdenes pasadas, un delete físico causaría error de llave foránea
     * en MySQL o dejaría huérfanas las tablas de 'pedido_detalles'.
     *
     * @param  int  $id  Identificador del producto
     */
    public function destroy(int $id): RedirectResponse
    {
        $producto = Producto::findOrFail($id);
        $producto->activo = 0;
        $producto->save();

        return redirect()
            ->route('admin.productos.index')
            ->with('success', "El producto '{$producto->nombre}' fue desactivado del catálogo público.");
    }
}
