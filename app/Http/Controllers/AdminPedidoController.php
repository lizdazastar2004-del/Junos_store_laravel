<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPedidoController extends Controller
{
    /**
     * Muestra el listado de pedidos con filtros por estado y buscador.
     */
    public function index(Request $request): View
    {
        $estado = $request->query('estado');
        $buscar = $request->query('buscar');

        $query = Pedido::with(['usuario', 'detalles.producto'])
            ->orderBy('id_pedido', 'desc');

        if (! empty($estado) && $estado !== 'todos') {
            $mapeoFiltro = [
                'pendiente' => ['pendiente', 'PENDIENTE'],
                'confirmado' => ['confirmado', 'PAGADO'],
                'en_preparacion' => ['en_preparacion', 'EN_PROCESO'],
                'enviado' => ['enviado', 'ENVIADO'],
                'entregado' => ['entregado', 'ENTREGADO'],
                'cancelado' => ['cancelado', 'CANCELADO'],
            ];
            $query->whereIn('estado', $mapeoFiltro[$estado] ?? [$estado, strtoupper($estado)]);
        }

        if (! empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_pedido', 'like', "%{$buscar}%")
                    ->orWhere('numero_pedido', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', function ($u) use ($buscar) {
                        $u->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('apellido', 'like', "%{$buscar}%")
                            ->orWhere('email', 'like', "%{$buscar}%");
                    });
            });
        }

        $pedidos = $query->paginate(15)->withQueryString();

        // Conteo de pedidos por estado para badges
        $conteos = [
            'todos' => Pedido::count(),
            'pendiente' => Pedido::whereIn('estado', ['pendiente', 'PENDIENTE'])->count(),
            'confirmado' => Pedido::whereIn('estado', ['confirmado', 'PAGADO'])->count(),
            'en_preparacion' => Pedido::whereIn('estado', ['en_preparacion', 'EN_PROCESO'])->count(),
            'enviado' => Pedido::whereIn('estado', ['enviado', 'ENVIADO'])->count(),
            'entregado' => Pedido::whereIn('estado', ['entregado', 'ENTREGADO'])->count(),
            'cancelado' => Pedido::whereIn('estado', ['cancelado', 'CANCELADO'])->count(),
        ];

        return view('admin.pedidos.index', compact('pedidos', 'estado', 'buscar', 'conteos'));
    }

    /**
     * Muestra el detalle visual completo de un pedido específico.
     */
    public function show(int $id): View
    {
        $pedido = Pedido::with(['usuario', 'detalles.producto.imagenes', 'pago'])->findOrFail($id);

        return view('admin.pedidos.detalle', compact('pedido'));
    }

    /**
     * Actualiza el estado de un pedido (ej: de 'pendiente' a 'enviado').
     */
    public function cambiarEstado(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'estado' => 'required|in:pendiente,confirmado,en_preparacion,enviado,entregado,cancelado',
        ]);

        $pedido = Pedido::findOrFail($id);
        $estadoAnterior = $pedido->estadoFormateado;
        $estadoInput = strtolower($request->input('estado'));

        // Mapeo seguro con los enums nativos de la base de datos MySQL
        $mapeoEnum = [
            'pendiente' => 'PENDIENTE',
            'confirmado' => 'PAGADO',
            'en_preparacion' => 'EN_PROCESO',
            'enviado' => 'ENVIADO',
            'entregado' => 'ENTREGADO',
            'cancelado' => 'CANCELADO',
        ];

        try {
            $pedido->estado = $mapeoEnum[$estadoInput] ?? strtoupper($estadoInput);
            $pedido->save();
        } catch (\Throwable) {
            // Si el motor admite directamente minúsculas (ej: SQLite en tests)
            $pedido->estado = $estadoInput;
            $pedido->save();
        }

        return redirect()->back()->with(
            'success',
            "El estado del pedido #{$pedido->id_pedido} se actualizó de '{$estadoAnterior}' a '{$pedido->estadoFormateado}'."
        );
    }
}
