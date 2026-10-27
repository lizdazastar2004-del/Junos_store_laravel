<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\CodigoDescuento;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Pedidos
 *
 * Gestiona el proceso completo de compra:
 * - Checkout (formulario de datos de envío)
 * - Confirmación de pedido
 * - Historial de pedidos
 * - Detalle de pedidos
 */
class PedidoController extends Controller
{
    /**
     * Mostrar el formulario de checkout
     *
     * GET /checkout
     */
    public function checkout()
    {
        // Obtener el carrito activo del usuario
        $carrito = Carrito::where('id_usuario', Auth::id())
            ->where('estado', 'activo')
            ->first();

        if (! $carrito) {
            return redirect()->route('carrito.index')
                ->with('warning', 'Tu carrito está vacío');
        }

        // Obtener los items del carrito
        $items = CarritoDetalle::where('id_carrito', $carrito->id_carrito)
            ->with(['producto.imagenes'])
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')
                ->with('warning', 'Tu carrito está vacío');
        }

        // Calcular totales
        $subtotal = $carrito->calcularSubtotal();
        $descuento = $carrito->descuento_aplicado ?? 0;

        $montoMinEnvioGratis = config('constants.SHIPPING_FREE_MIN', 150000);
        $costoEnvio = config('constants.SHIPPING_COST', 8000);
        $envio = ($subtotal - $descuento) >= $montoMinEnvioGratis ? 0 : $costoEnvio;

        $total = max(0, $subtotal - $descuento + $envio);

        // Obtener direcciones guardadas del usuario
        $direcciones = Auth::user()->direcciones()->where('activa', 1)->get();

        // Obtener métodos de pago disponibles
        $metodosPago = DB::table('metodos_pago')
            ->where('activo', 1)
            ->get();

        return view('pedidos.checkout', compact(
            'items',
            'subtotal',
            'descuento',
            'envio',
            'total',
            'carrito',
            'direcciones',
            'metodosPago'
        ));
    }

    /**
     * Confirmar y crear el pedido
     *
     * POST /checkout/confirmar
     */
    public function confirmar(Request $request)
    {
        // Validar datos del formulario
        $validated = $request->validate([
            'direccion_id' => 'nullable|exists:direcciones,id_direccion',
            'calle' => 'required_without:direccion_id|string|max:255',
            'ciudad' => 'required_without:direccion_id|string|max:100',
            'departamento' => 'required_without:direccion_id|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'telefono' => 'required|string|max:20',
            'metodo_pago' => 'required|string|in:transferencia,efectivo,tarjeta,pse',
            'notas' => 'nullable|string|max:500',
        ]);

        // Obtener el carrito activo
        $carrito = Carrito::where('id_usuario', Auth::id())
            ->where('estado', 'activo')
            ->first();

        if (! $carrito) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío');
        }

        // Obtener items del carrito
        $items = CarritoDetalle::where('id_carrito', $carrito->id_carrito)
            ->with('producto')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío');
        }

        // Verificar stock de todos los productos
        foreach ($items as $item) {
            $stockDisponible = (int) ($item->producto ? $item->producto->stock_actual : 0);

            if ($stockDisponible < $item->cantidad) {
                return redirect()->route('carrito.index')
                    ->with('error', "Stock insuficiente para {$item->producto->nombre}");
            }
        }

        // Construir dirección de entrega
        if ($request->filled('direccion_id')) {
            $direccion = DB::table('direcciones')
                ->where('id_direccion', $request->direccion_id)
                ->where('id_usuario', Auth::id())
                ->first();

            $direccionTexto = "{$direccion->calle}, {$direccion->ciudad}, {$direccion->departamento}";
            if ($direccion->codigo_postal) {
                $direccionTexto .= ", CP {$direccion->codigo_postal}";
            }
            $direccionTexto .= ", Tel: {$request->telefono}";
        } else {
            $direccionTexto = "{$request->calle}, {$request->ciudad}, {$request->departamento}";
            if ($request->codigo_postal) {
                $direccionTexto .= ", CP {$request->codigo_postal}";
            }
            $direccionTexto .= ", Tel: {$request->telefono}";
        }

        // Calcular totales
        $subtotal = $carrito->calcularSubtotal();
        $descuento = $carrito->descuento_aplicado ?? 0;

        $montoMinEnvioGratis = config('constants.SHIPPING_FREE_MIN', 150000);
        $costoEnvio = config('constants.SHIPPING_COST', 8000);
        $envio = ($subtotal - $descuento) >= $montoMinEnvioGratis ? 0 : $costoEnvio;

        $total = max(0, $subtotal - $descuento + $envio);

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // Crear el pedido
            $pedido = Pedido::create([
                'id_usuario' => Auth::id(),
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'costo_envio' => $envio,
                'total' => $total,
                'estado' => 'pendiente',
                'direccion_entrega' => $direccionTexto,
                'notas' => $request->notas,
                'codigo_descuento_id' => $carrito->codigo_descuento_id,
            ]);

            // Crear los detalles del pedido
            foreach ($items as $item) {
                PedidoDetalle::create([
                    'id_pedido' => $pedido->id_pedido,
                    'id_producto' => $item->id_producto,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->cantidad * $item->precio_unitario,
                ]);

                // Descontar del inventario del producto
                Producto::where('id_producto', $item->id_producto)
                    ->decrement('stock_actual', $item->cantidad);

                // Registrar movimiento de inventario
                DB::table('movimientos_inventario')->insert([
                    'id_producto' => $item->id_producto,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $item->cantidad,
                    'motivo' => "Venta - Pedido #{$pedido->id_pedido}",
                    'id_usuario' => Auth::id(),
                    'creado_en' => now(),
                ]);
            }

            // Crear registro de pago
            $metodoPagoId = DB::table('metodos_pago')
                ->where('nombre', $request->metodo_pago)
                ->value('id_metodo_pago');

            Pago::create([
                'id_pedido' => $pedido->id_pedido,
                'id_metodo_pago' => $metodoPagoId ?? 1,
                'monto' => $total,
                'estado' => 'pendiente',
                'fecha_pago' => now(),
            ]);

            // Marcar código de descuento como usado
            if ($carrito->codigo_descuento_id) {
                $codigo = CodigoDescuento::find($carrito->codigo_descuento_id);
                if ($codigo) {
                    $codigo->incrementarUsos();
                }
            }

            // Marcar carrito como completado
            $carrito->estado = 'completado';
            $carrito->save();

            // Crear notificación para el usuario
            Notificacion::create([
                'id_usuario' => Auth::id(),
                'tipo' => 'pedido_nuevo',
                'titulo' => "Pedido #{$pedido->id_pedido} Confirmado",
                'mensaje' => 'Hemos recibido tu pedido correctamente. Puedes hacer seguimiento desde tus pedidos.',
                'leida' => 0,
            ]);

            // Crear notificaciones para administradores
            $admins = DB::table('usuarios')
                ->where('id_rol', 1)
                ->where('activo', 1)
                ->pluck('id_usuario');

            foreach ($admins as $adminId) {
                Notificacion::create([
                    'id_usuario' => $adminId,
                    'tipo' => 'nuevo_pedido',
                    'titulo' => "Nuevo Pedido #{$pedido->id_pedido}",
                    'mensaje' => 'El cliente '.Auth::user()->nombre_completo.' ha realizado un pedido por $'.number_format($total, 0, ',', '.').' COP.',
                    'leida' => 0,
                ]);
            }

            // Limpiar sesión del carrito
            session()->forget(['carrito_count']);

            DB::commit();

            // Redireccionar a confirmación
            return redirect()->route('pedidos.confirmacion', $pedido->id_pedido)
                ->with('success', '¡Pedido realizado exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('checkout')
                ->with('error', 'Ocurrió un error al procesar tu pedido. Intenta de nuevo.');
        }
    }

    /**
     * Mostrar página de confirmación del pedido
     *
     * GET /pedidos/confirmacion/{id}
     */
    public function confirmacion($id)
    {
        $pedido = Pedido::with(['detalles.producto.imagenes', 'usuario', 'pago.metodoPago'])
            ->where('id_pedido', $id)
            ->where('id_usuario', Auth::id())
            ->firstOrFail();

        return view('pedidos.confirmacion', compact('pedido'));
    }

    /**
     * Mostrar historial de pedidos del usuario
     *
     * GET /pedidos
     */
    public function historial()
    {
        $pedidos = Pedido::with(['detalles.producto', 'pago'])
            ->where('id_usuario', Auth::id())
            ->orderBy('creado_en', 'desc')
            ->paginate(10);

        return view('pedidos.historial', compact('pedidos'));
    }

    /**
     * Mostrar detalle de un pedido específico
     *
     * GET /pedidos/{id}
     */
    public function detalle($id)
    {
        $pedido = Pedido::with(['detalles.producto.imagenes', 'pago.metodoPago', 'usuario'])
            ->where('id_pedido', $id)
            ->where('id_usuario', Auth::id())
            ->firstOrFail();

        return view('pedidos.detalle', compact('pedido'));
    }
}
