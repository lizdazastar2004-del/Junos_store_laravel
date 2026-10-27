<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * ==============================================================================
 * CONTROLADOR: AuthController
 * ==============================================================================
 * Gestiona el ciclo completo de autenticación de usuarios en Juno's Store:
 * 1. Visualización de formularios de acceso y registro.
 * 2. Validación de credenciales contra la tabla personalizada 'usuarios'.
 * 3. Encriptación y verificación de contraseñas con el algoritmo Bcrypt.
 * 4. Regeneración y protección contra ataques de fijación de sesión (Session Fixation).
 * 5. Redirección inteligente según el rol del usuario (Admin ➔ Panel / Cliente ➔ Mi Espacio).
 * ==============================================================================
 */
class AuthController extends Controller
{
    /**
     * Muestra la vista con el formulario de inicio de sesión.
     *
     * @return View Vista 'auth.login'
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa la solicitud de inicio de sesión (POST /login).
     *
     * Pasos de seguridad:
     * 1. Valida que el email tenga formato válido y la contraseña no esté vacía.
     * 2. Busca al usuario en la base de datos por su email.
     * 3. Compara el hash de la contraseña usando Hash::check().
     * 4. Valida que el campo 'activo' sea 1 (usuario habilitado por la administración).
     * 5. Inicia la sesión mediante el guard de autenticación de Laravel: Auth::login().
     * 6. Regenera el ID de sesión ($request->session()->regenerate()) para prevenir Session Hijacking.
     * 7. Redirige a la vista correspondiente según el rol del usuario.
     *
     * @param  Request  $request  Petición entrante con credenciales
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validación de campos obligatorios
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'password.required' => 'Por favor ingresa tu contraseña.',
        ]);

        // 2. Búsqueda del usuario en la base de datos
        $usuario = Usuario::where('email', $request->email)->first();

        // 3. Verificación de existencia del usuario
        if (! $usuario) {
            return back()->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])->withInput();
        }

        // 4. Verificación de coincidencia de contraseña hasheada
        if (! Hash::check($request->password, $usuario->password_hash)) {
            return back()->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])->withInput();
        }

        // 5. Verificación de cuenta activa (no suspendida)
        if (! $usuario->activo) {
            return back()->withErrors(['email' => 'Tu cuenta ha sido desactivada. Por favor contacta al administrador.'])->withInput();
        }

        // 6. Inicio de sesión mediante Auth Facade
        Auth::login($usuario, $request->boolean('remember'));

        // 7. Regenerar sesión para seguridad
        $request->session()->regenerate();

        // 8. Actualizar marca de último acceso
        $usuario->ultimo_acceso = now();
        $usuario->save();

        // 9. Redirección diferenciada:
        // - Administradores -> /admin (Dashboard de control y métricas)
        // - Clientes -> /mi-cuenta (Portal personal kawaii del cliente)
        $destino = $usuario->isAdmin() ? route('admin.dashboard') : route('cliente.dashboard');

        return redirect()->intended($destino)
            ->with('success', '¡Bienvenido de nuevo, '.$usuario->nombre.'! 🐾');
    }

    /**
     * Cierra la sesión activa del usuario y destruye los datos de sesión.
     *
     * @param  Request  $request  Petición HTTP
     */
    public function logout(Request $request): RedirectResponse
    {
        // Cierra la sesión en el guard de autenticación
        Auth::logout();

        // Invalida la sesión actual del usuario
        $request->session()->invalidate();

        // Regenera el token CSRF para prevenir ataques Cross-Site Request Forgery
        $request->session()->regenerateToken();

        return redirect()->route('tienda.index')->with('success', 'Has cerrado sesión exitosamente. ¡Vuelve pronto!');
    }

    /**
     * Muestra el formulario de registro para nuevos clientes.
     *
     * @return View Vista 'auth.registro'
     */
    public function showRegistro(): View
    {
        return view('auth.registro');
    }

    /**
     * Procesa la creación de una nueva cuenta de cliente (POST /registro).
     *
     * @param  Request  $request  Datos del formulario
     */
    public function registro(Request $request): RedirectResponse
    {
        // Validación de datos con reglas estrictas
        $request->validate([
            'nombre' => 'required|max:80',
            'apellido' => 'required|max:80',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:6|confirmed',
            'telefono' => 'nullable|max:30',
        ], [
            'email.unique' => 'Este correo ya se encuentra registrado.',
            'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',
            'password.confirmed' => 'Las contraseñas ingresadas no coinciden.',
        ]);

        // Creación del usuario con rol 'Cliente' (id_rol = 2) y contraseña hasheada
        $usuario = Usuario::create([
            'id_rol' => 2, // Rol 2 = Cliente
            'nombre' => trim($request->nombre),
            'apellido' => trim($request->apellido),
            'email' => strtolower(trim($request->email)),
            'password_hash' => Hash::make($request->password),
            'telefono' => trim($request->telefono),
            'activo' => 1,
            'email_verificado' => 0,
        ]);

        // Autenticar automáticamente al nuevo usuario registrado
        Auth::login($usuario);

        // Redirigir a su nuevo portal del cliente
        return redirect()->route('cliente.dashboard')
            ->with('success', '¡Registro exitoso! Te damos la bienvenida a Juno\'s Store 🌸');
    }
}
