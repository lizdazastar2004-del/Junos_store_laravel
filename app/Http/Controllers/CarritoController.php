<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\CodigoDescuento;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador del Carrito de Compras
 *
 * Maneja todas las operaciones relacionadas con el carrito:
 * - Ver carrito
 * - Agregar productos
 * - Actualizar cantidades
 * - Eliminar items
 * - Aplicar códigos de descuento
 */
class CarritoController extends Controller
{
    /**
     * Mostrar el carrito de compras del usuario
     *
     * GET /carrito
     */
    public function index()
    {
        // Obtener o crear el carrito activo del usuario
        $carrito = $this->obtenerCarritoActivo();

        // Obtener los items del carrito con información del producto e imágenes
        $items = CarritoDetalle::where('id_carrito', $carrito->id_carrito)
            ->with(['producto.imagenes'])
            ->get();

        // Calcular totales
        $subtotal = $carrito->calcularSubtotal();
        $descuento = $carrito->descuento_aplicado ?? 0;

        // Calcular envío (gratis si supera monto mínimo)
        $montoMinEnvioGratis = config('constants.SHIPPING_FREE_MIN', 150000);
        $costoEnvio = config('constants.SHIPPING_COST', 8000);
        $envio = ($subtotal - $descuento) >= $montoMinEnvioGratis ? 0 : $costoEnvio;

        $total = max(0, $subtotal - $descuento + $envio);

        // Información del código de descuento aplicado
        $codigoAplicado = null;
        if ($carrito->codigo_descuento_id) {
            $codigoAplicado = CodigoDescuento::find($carrito->codigo_descuento_id);
        }

        return view('carrito.index', compact(
            'items',
            'subtotal',
            'descuento',
            'envio',
            'total',
            'codigoAplicado',
            'carrito'
        ));
    }

    /**
     * Agregar un producto al carrito
     *
     * POST /carrito/agregar
     */
    public function agregar(Request $request)
    {
        // Validar datos de entrada
        $request->validate([
            'producto_id' => 'required|integer|exists:productos,id_producto',
            'cantidad' => 'required|integer|min:1',
        ]);

        $productoId = $request->producto_id;
        $cantidad = $request->cantidad;

        // Obtener el producto
        $producto = Producto::find($productoId);

        if (! $producto || ! $producto->activo) {
            return $this->jsonError('Producto no disponible');
        }

        // Verificar stock disponible directamente del producto
        $stockDisponible = (int) $producto->stock_actual;

        if ($stockDisponible < $cantidad) {
            return $this->jsonError("Stock insuficiente. Disponible: {$stockDisponible} unidades");
        }

        // Obtener o crear el carrito activo
        $carrito = $this->obtenerCarritoActivo();

        // Verificar si el producto ya está en el carrito
        $itemExistente = CarritoDetalle::where('id_carrito', $carrito->id_carrito)
            ->where('id_producto', $productoId)
            ->first();

        if ($itemExistente) {
            // Actualizar cantidad si ya existe
            $nuevaCantidad = $itemExistente->cantidad + $cantidad;

            // Verificar stock para la nueva cantidad
            if ($stockDisponible < $nuevaCantidad) {
                return $this->jsonError("No hay suficiente stock. Disponible: {$stockDisponible}, en carrito: {$itemExistente->cantidad}");
            }

            $itemExistente->cantidad = $nuevaCantidad;
            $itemExistente->save();
        } else {
            // Crear nuevo item en el carrito
            CarritoDetalle::create([
                'id_carrito' => $carrito->id_carrito,
                'id_producto' => $productoId,
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio,
            ]);
        }

        // Actualizar timestamp del carrito
        $carrito->touch();

        // Contar items totales
        $conteoItems = $carrito->contarItems();

        // Guardar en sesión para el contador del header
        session(['carrito_count' => $conteoItems]);

        // Responder según el tipo de petición
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'count' => $conteoItems,
                'mensaje' => 'Producto agregado al carrito',
            ]);
        }

        return redirect()->route('carrito.index')
            ->with('success', 'Producto agregado al carrito');
    }

    /**
     * Actualizar la cantidad de un item del carrito
     *
     * POST /carrito/actualizar
     */
    public function actualizar(Request $request)
    {
        // Validar datos
        $request->validate([
            'item_id' => 'required|integer',
            'cantidad' => 'required|integer|min:1',
        ]);

        $itemId = $request->item_id;
        $cantidad = $request->cantidad;

        // Obtener el carrito activo
        $carrito = $this->obtenerCarritoActivo();

        // Verificar que el item pertenece al carrito del usuario
        $item = CarritoDetalle::where('id_carrito_detalle', $itemId)
            ->where('id_carrito', $carrito->id_carrito)
            ->first();

        if (! $item) {
            return response()->json([
                'ok' => false,
                'error' => 'Item no encontrado en tu carrito',
            ], 404);
        }

        // Verificar stock disponible directamente del producto
        $stockDisponible = (int) ($item->producto ? $item->producto->stock_actual : 0);

        if ($stockDisponible < $cantidad) {
            return response()->json([
                'ok' => false,
                'error' => "Stock insuficiente. Disponible: {$stockDisponible} unidades",
            ], 400);
        }

        // Actualizar cantidad
        $item->cantidad = $cantidad;
        $item->save();

        // Actualizar timestamp del carrito
        $carrito->touch();

        // Recalcular totales
        $subtotal = $carrito->calcularSubtotal();
        $descuento = $carrito->descuento_aplicado ?? 0;

        $montoMinEnvioGratis = config('constants.SHIPPING_FREE_MIN', 150000);
        $costoEnvio = config('constants.SHIPPING_COST', 8000);
        $envio = ($subtotal - $descuento) >= $montoMinEnvioGratis ? 0 : $costoEnvio;

        $total = max(0, $subtotal - $descuento + $envio);
        $count = $carrito->contarItems();

        session(['carrito_count' => $count]);

        return response()->json([
            'ok' => true,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'envio' => $envio,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * Eliminar un item del carrito
     *
     * POST /carrito/eliminar
     */
    public function eliminar(Request $request)
    {
        // Validar datos
        $request->validate([
            'item_id' => 'required|integer',
        ]);

        $itemId = $request->item_id;

        // Obtener el carrito activo
        $carrito = $this->obtenerCarritoActivo();

        // Verificar que el item pertenece al carrito del usuario
        $item = CarritoDetalle::where('id_carrito_detalle', $itemId)
            ->where('id_carrito', $carrito->id_carrito)
            ->first();

        if (! $item) {
            return response()->json([
                'ok' => false,
                'error' => 'Item no encontrado',
            ], 404);
        }

        // Eliminar el item
        $item->delete();

        // Actualizar timestamp del carrito
        $carrito->touch();

        // Recalcular totales
        $subtotal = $carrito->calcularSubtotal();
        $descuento = $carrito->descuento_aplicado ?? 0;

        // Si el carrito quedó vacío, quitar descuento
        if ($subtotal <= 0) {
            $descuento = 0;
            $carrito->descuento_aplicado = 0;
            $carrito->codigo_descuento_id = null;
            $carrito->save();
        }

        $montoMinEnvioGratis = config('constants.SHIPPING_FREE_MIN', 150000);
        $costoEnvio = config('constants.SHIPPING_COST', 8000);
        $envio = ($subtotal - $descuento) >= $montoMinEnvioGratis ? 0 : $costoEnvio;

        $total = max(0, $subtotal - $descuento + $envio);
        $count = $carrito->contarItems();

        session(['carrito_count' => $count]);

        return response()->json([
            'ok' => true,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'envio' => $envio,
            'total' => $total,
            'count' => $count,
            'mensaje' => 'Producto eliminado del carrito',
        ]);
    }

    /**
     * Aplicar un código de descuento al carrito
     *
     * POST /carrito/descuento
     */
    public function aplicarDescuento(Request $request)
    {
        // Validar datos
        $request->validate([
            'codigo' => 'required|string',
        ]);

        $codigoStr = strtoupper(trim($request->codigo));

        // Buscar el código de descuento
        $codigo = CodigoDescuento::buscarPorCodigo($codigoStr);

        if (! $codigo) {
            return response()->json([
                'ok' => false,
                'error' => 'Código de descuento no válido',
            ], 400);
        }

        // Verificar si el código es válido
        if (! $codigo->esValido()) {
            return response()->json([
                'ok' => false,
                'error' => 'El código de descuento ha expirado o alcanzó el límite de usos',
            ], 400);
        }

        // Obtener el carrito
        $carrito = $this->obtenerCarritoActivo();
        $subtotal = $carrito->calcularSubtotal();

        // Calcular el descuento
        $montoDescuento = $codigo->calcularDescuento($subtotal);

        if ($montoDescuento <= 0) {
            return response()->json([
                'ok' => false,
                'error' => 'El pedido no alcanza el monto mínimo para este código',
            ], 400);
        }

        // Aplicar descuento al carrito
        $carrito->descuento_aplicado = $montoDescuento;
        $carrito->codigo_descuento_id = $codigo->id_codigo_descuento;
        $carrito->save();

        // Recalcular totales
        $montoMinEnvioGratis = config('constants.SHIPPING_FREE_MIN', 150000);
        $costoEnvio = config('constants.SHIPPING_COST', 8000);
        $envio = ($subtotal - $montoDescuento) >= $montoMinEnvioGratis ? 0 : $costoEnvio;

        $total = max(0, $subtotal - $montoDescuento + $envio);

        // Mensaje de confirmación
        $valorTexto = $codigo->tipo === 'porcentaje'
            ? "{$codigo->valor}%"
            : number_format($codigo->valor, 0, ',', '.').' COP';

        return response()->json([
            'ok' => true,
            'descuento' => $montoDescuento,
            'envio' => $envio,
            'total' => $total,
            'mensaje' => "Código aplicado: -{$valorTexto}",
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
     * MÉTODOS AUXILIARES PRIVADOS
     * ══════════════════════════════════════════════════════════════════ */

    /**
     * Obtener o crear el carrito activo del usuario autenticado
     */
    private function obtenerCarritoActivo(): Carrito
    {
        $usuarioId = Auth::id();

        // Buscar carrito activo existente (enum ACTIVO)
        $carrito = Carrito::where('id_usuario', $usuarioId)
            ->whereIn('estado', ['ACTIVO', 'activo'])
            ->first();

        // Si no existe, crear uno nuevo
        if (! $carrito) {
            $carrito = Carrito::create([
                'id_usuario' => $usuarioId,
                'estado' => 'ACTIVO',
            ]);
        }

        return $carrito;
    }

    /**
     * Respuesta JSON de error
     */
    private function jsonError(string $mensaje, int $codigo = 400)
    {
        return response()->json([
            'ok' => false,
            'error' => $mensaje,
        ], $codigo);
    }
}
