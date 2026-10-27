<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * ==============================================================================
 * TEST: SecurityAndMiddlewareTest
 * ==============================================================================
 * Suite de pruebas de seguridad para verificar:
 * 1. Protección de rutas con middleware 'auth' y 'admin'.
 * 2. Bloqueo 403 Forbidden a usuarios con rol 'Cliente' que intenten acceder a /admin.
 * 3. Redirección de visitantes anónimos al login.
 * 4. Control de visibilidad condicional en el Navbar / Sidebar de las vistas.
 * ==============================================================================
 */
class SecurityAndMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Usuario $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles requeridos
        $rolAdmin = Rol::create([
            'id_rol' => 1,
            'nombre' => 'Administrador',
            'descripcion' => 'Control total del sistema',
        ]);

        $rolCliente = Rol::create([
            'id_rol' => 2,
            'nombre' => 'Cliente',
            'descripcion' => 'Comprador de la tienda',
        ]);

        // Crear usuario administrador
        $this->admin = Usuario::create([
            'id_rol' => $rolAdmin->id_rol,
            'nombre' => 'Juno',
            'apellido' => 'Admin',
            'email' => 'admin@junosstore.local',
            'password_hash' => Hash::make('admin123'),
            'activo' => 1,
        ]);

        // Crear usuario cliente
        $this->cliente = Usuario::create([
            'id_rol' => $rolCliente->id_rol,
            'nombre' => 'Mimi',
            'apellido' => 'Clienta',
            'email' => 'mimi@junosstore.local',
            'password_hash' => Hash::make('cliente123'),
            'activo' => 1,
        ]);
    }

    /**
     * Un visitante sin sesión debe ser redirigido a login al intentar acceder al panel admin.
     */
    public function test_guest_is_redirected_to_login_when_accessing_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /**
     * Un visitante sin sesión debe ser redirigido a login al intentar entrar a crear productos.
     */
    public function test_guest_cannot_access_product_management(): void
    {
        $response = $this->get('/admin/productos');

        $response->assertRedirect('/login');
    }

    /**
     * Un usuario con rol de Cliente recibe HTTP 403 Forbidden al intentar ingresar a /admin.
     */
    public function test_authenticated_client_receives_403_forbidden_on_admin_dashboard(): void
    {
        $response = $this->actingAs($this->cliente)->get('/admin');

        $response->assertStatus(403);
        $response->assertSee('Acceso 403 Prohibido');
        $response->assertSee('Área Exclusiva de Administradores');
    }

    /**
     * Un usuario con rol de Cliente no puede manipular productos ni pedidos en el panel admin.
     */
    public function test_authenticated_client_receives_403_on_admin_orders_and_products(): void
    {
        $responseProductos = $this->actingAs($this->cliente)->get('/admin/productos');
        $responseProductos->assertStatus(403);

        $responsePedidos = $this->actingAs($this->cliente)->get('/admin/pedidos');
        $responsePedidos->assertStatus(403);
    }

    /**
     * Un usuario con rol de Administrador puede acceder exitosamente a todas las rutas /admin.
     */
    public function test_administrator_can_access_admin_dashboard_and_modules(): void
    {
        $responseDashboard = $this->actingAs($this->admin)->get('/admin');
        $responseDashboard->assertStatus(200);
        $responseDashboard->assertSee('Panel Administrativo');

        $responseProductos = $this->actingAs($this->admin)->get('/admin/productos');
        $responseProductos->assertStatus(200);
    }

    /**
     * El enlace al Panel Admin en el Navbar solo es visible para administradores.
     */
    public function test_admin_panel_link_in_navbar_is_only_visible_to_admins(): void
    {
        // 1. Visitante anónimo no ve 'Panel Admin'
        $responseGuest = $this->get('/');
        $responseGuest->assertDontSee('Panel Admin');

        // 2. Cliente autenticado no ve 'Panel Admin'
        $responseCliente = $this->actingAs($this->cliente)->get('/');
        $responseCliente->assertDontSee('Panel Admin');
        $responseCliente->assertSee('Mi Cuenta');

        // 3. Administrador autenticado sí ve 'Panel Admin'
        $responseAdmin = $this->actingAs($this->admin)->get('/');
        $responseAdmin->assertSee('Panel Admin');
    }

    /**
     * El botón de cargar datos de prueba solo puede ser invocado por administradores.
     */
    public function test_only_administrator_can_trigger_demo_data_seeding(): void
    {
        // 1. Cliente recibe 403
        $responseCliente = $this->actingAs($this->cliente)->post('/admin/mantenimiento/cargar-datos-prueba');
        $responseCliente->assertStatus(403);

        // 2. Administrador puede ejecutar la acción y es redirigido con flash message de éxito
        $responseAdmin = $this->actingAs($this->admin)->post('/admin/mantenimiento/cargar-datos-prueba');
        $responseAdmin->assertRedirect(route('admin.dashboard'));
        $responseAdmin->assertSessionHas('success');
    }
}
