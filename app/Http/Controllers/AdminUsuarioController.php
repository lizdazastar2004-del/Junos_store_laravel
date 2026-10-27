<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUsuarioController extends Controller
{
    /**
     * Muestra el listado de usuarios con filtros y conteo de pedidos.
     */
    public function index(Request $request): View
    {
        $buscar = $request->query('buscar');
        $rol = $request->query('rol');

        $query = Usuario::with(['rol'])
            ->withCount('pedidos')
            ->orderBy('id_usuario', 'desc');

        if (! empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        if ($rol === 'admin') {
            $query->where('id_rol', 1);
        } elseif ($rol === 'cliente') {
            $query->where('id_rol', '!=', 1);
        }

        $usuarios = $query->paginate(15)->withQueryString();

        $totalUsuarios = Usuario::count();
        $totalClientes = Usuario::where('id_rol', '!=', 1)->count();
        $totalAdmins = Usuario::where('id_rol', 1)->count();

        return view('admin.usuarios.index', compact('usuarios', 'buscar', 'rol', 'totalUsuarios', 'totalClientes', 'totalAdmins'));
    }

    /**
     * Alterna el estado activo/inactivo de un usuario.
     */
    public function toggleActivo(int $id): RedirectResponse
    {
        $usuario = Usuario::findOrFail($id);

        // Prevenir que el admin actual se desactive a sí mismo
        if ($usuario->id_usuario === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes desactivar tu propia cuenta de administrador.');
        }

        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        $estadoTexto = $usuario->activo ? 'activada' : 'desactivada';

        return redirect()->back()->with(
            'success',
            "La cuenta de '{$usuario->nombre_completo}' fue {$estadoTexto} exitosamente."
        );
    }
}
