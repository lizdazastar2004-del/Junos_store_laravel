<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Direccion;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * ==============================================================================
 * CLASE: ClienteController
 * ==============================================================================
 * Controlador exclusivo para el rol de Cliente (Comprador final).
 *
 * Responsabilidad:
 * Proporcionar al cliente un portal personal ("Mi Cuenta / Panel del Cliente")
 * con una experiencia estética, acogedora y kawaii, completamente diferente
 * a la perspectiva cuantitativa del panel administrativo.
 *
 * Aquí el cliente gestiona:
 * 1. Resumen de bienvenida y actividad reciente (KPIs de comprador).
 * 2. Visualización y seguimiento de sus pedidos con fotos y estados.
 * 3. Libreta de direcciones de envío (crear, listar y eliminar).
 * 4. Actualización de datos de perfil y cambio seguro de contraseña.
 * ==============================================================================
 */
class ClienteController extends Controller
{
    /**
     * Muestra el panel principal del cliente.
     *
     * Flujo de datos:
     * - Obtiene el usuario autenticado mediante Auth::user().
     * - Consulta sus pedidos ordenados del más reciente al más antiguo con Eager Loading.
     * - Consulta sus direcciones guardadas activas.
     * - Consulta su carrito actual para mostrar el botón de reanudar compra si tiene productos.
     *
     * @return View Retorna la vista Blade 'cliente.dashboard'
     */
    public function dashboard(): View
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        // 1. Obtener los últimos pedidos del cliente con sus detalles y productos asociados
        // Usamos Eager Loading ('detalles.producto.imagenes') para optimizar consultas a la BD
        $pedidos = Pedido::with(['detalles.producto.imagenes', 'pago.metodoPago'])
            ->where('id_usuario', $usuario->id_usuario)
            ->orderBy('id_pedido', 'desc')
            ->get();

        // 2. Métricas rápidas para las tarjetas de bienvenida del cliente
        $totalPedidos = $pedidos->count();
        $pedidosActivos = $pedidos->whereIn('estado', ['pendiente', 'confirmado', 'en_preparacion', 'enviado', 'PENDIENTE', 'PAGADO', 'EN_PROCESO', 'ENVIADO'])->count();
        $pedidosEntregados = $pedidos->whereIn('estado', ['entregado', 'ENTREGADO'])->count();
        $totalInvertido = $pedidos->whereNotIn('estado', ['cancelado', 'CANCELADO'])->sum('total');

        // 3. Direcciones de despacho guardadas por el cliente
        $direcciones = Direccion::where('id_usuario', $usuario->id_usuario)
            ->where('activo', 1)
            ->orderBy('id_direccion', 'desc')
            ->get();

        // 4. Carrito de compras activo en este momento
        $carrito = Carrito::where('id_usuario', $usuario->id_usuario)
            ->whereIn('estado', ['activo', 'ACTIVO'])
            ->with(['detalles.producto.imagenes'])
            ->first();

        // Saludo personalizado según la hora del día en el huso horario local
        $hora = (int) date('H');
        if ($hora >= 5 && $hora < 12) {
            $saludo = '¡Buenos días!';
        } elseif ($hora >= 12 && $hora < 19) {
            $saludo = '¡Buenas tardes!';
        } else {
            $saludo = '¡Buenas noches!';
        }

        return view('cliente.dashboard', compact(
            'usuario',
            'pedidos',
            'totalPedidos',
            'pedidosActivos',
            'pedidosEntregados',
            'totalInvertido',
            'direcciones',
            'carrito',
            'saludo'
        ));
    }

    /**
     * Actualiza la información básica del perfil del cliente (nombre, apellido, teléfono).
     *
     * @param  Request  $request  Petición HTTP con los campos enviados desde el formulario
     * @return RedirectResponse Redirección con mensaje flash de éxito o error
     */
    public function actualizarPerfil(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        // Validación estricta de datos entrantes
        $request->validate([
            'nombre' => 'required|string|max:80',
            'apellido' => 'required|string|max:80',
            'telefono' => 'nullable|string|max:20',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
        ]);

        // Asignación controlada y guardado en la base de datos
        $usuario->nombre = trim($request->input('nombre'));
        $usuario->apellido = trim($request->input('apellido'));
        $usuario->telefono = trim($request->input('telefono'));
        $usuario->save();

        return redirect()->route('cliente.dashboard', ['tab' => 'perfil'])
            ->with('success', '¡Tus datos personales se han actualizado correctamente!');
    }

    /**
     * Cambia la contraseña del cliente previa verificación de la contraseña actual.
     *
     * @param  Request  $request  Petición HTTP con contraseña actual, nueva y confirmación
     */
    public function cambiarPassword(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        // Validar formato y coincidencia
        $request->validate([
            'password_actual' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password_actual.required' => 'Debes ingresar tu contraseña actual.',
            'password.required' => 'Debes ingresar tu nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        // Verificar que la contraseña actual ingresada coincida con el hash en la BD
        if (! Hash::check($request->input('password_actual'), $usuario->password_hash)) {
            return redirect()->route('cliente.dashboard', ['tab' => 'seguridad'])
                ->withErrors(['password_actual' => 'La contraseña actual no es correcta.'])
                ->withInput();
        }

        // Hashear la nueva contraseña con el algoritmo seguro de Laravel (Bcrypt)
        $usuario->password_hash = Hash::make($request->input('password'));
        $usuario->save();

        return redirect()->route('cliente.dashboard', ['tab' => 'seguridad'])
            ->with('success', '¡Tu contraseña ha sido cambiada con éxito!');
    }

    /**
     * Guarda una nueva dirección en la libreta de direcciones del cliente.
     *
     * @param  Request  $request  Petición con los campos de ubicación
     */
    public function guardarDireccion(Request $request): RedirectResponse
    {
        $request->validate([
            'titulo' => 'nullable|string|max:60',
            'calle' => 'required|string|max:180',
            'ciudad' => 'required|string|max:80',
            'departamento' => 'required|string|max:80',
            'codigo_postal' => 'nullable|string|max:10',
            'es_principal' => 'nullable|boolean',
        ], [
            'calle.required' => 'La dirección o calle es obligatoria.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'departamento.required' => 'El departamento/estado es obligatorio.',
        ]);

        $usuarioId = Auth::id();

        // Si el usuario marca esta dirección como principal, quitamos el flag de las anteriores
        if ($request->boolean('principal')) {
            Direccion::where('id_usuario', $usuarioId)->update(['principal' => 0]);
        }

        // Crear la nueva dirección en la tabla 'direcciones'
        Direccion::create([
            'id_usuario' => $usuarioId,
            'calle' => $request->input('calle'),
            'ciudad' => $request->input('ciudad'),
            'departamento' => $request->input('departamento'),
            'codigo_postal' => $request->input('codigo_postal'),
            'referencia' => $request->input('referencia'),
            'principal' => $request->boolean('principal') ? 1 : 0,
            'activo' => 1,
        ]);

        return redirect()->route('cliente.dashboard', ['tab' => 'direcciones'])
            ->with('success', '¡Nueva dirección de entrega guardada correctamente!');
    }

    /**
     * Elimina lógicamente una dirección del cliente.
     *
     * @param  int  $id  Identificador primario de la dirección a eliminar
     */
    public function eliminarDireccion(int $id): RedirectResponse
    {
        // Buscar la dirección asegurando que pertenezca al usuario autenticado (Seguridad)
        $direccion = Direccion::where('id_direccion', $id)
            ->where('id_usuario', Auth::id())
            ->firstOrFail();

        // Borrado lógico o físico
        $direccion->delete();

        return redirect()->route('cliente.dashboard', ['tab' => 'direcciones'])
            ->with('success', 'Dirección eliminada de tu libreta.');
    }
}
