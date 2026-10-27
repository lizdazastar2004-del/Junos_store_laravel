<?php

namespace Tests\Feature;

use App\Models\Direccion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * ==============================================================================
 * TEST: ClientePortalTest
 * ==============================================================================
 * Verifica la seguridad y funcionalidad del portal personal del cliente:
 * 1. Los invitados no autenticados son redirigidos al login si intentan ingresar a /mi-cuenta.
 * 2. Un cliente autenticado puede ver exitosamente su espacio personal (/mi-cuenta).
 * 3. El cliente puede actualizar su nombre y teléfono desde su panel.
 * ==============================================================================
 */
class ClientePortalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Un invitado no autenticado debe ser redirigido a la vista de login.
     */
    public function test_guest_is_redirected_to_login_when_accessing_client_portal(): void
    {
        $response = $this->get('/mi-cuenta');

        $response->assertRedirect('/login');
    }

    /**
     * Un cliente autenticado puede cargar correctamente su portal personal.
     */
    public function test_authenticated_client_can_access_dashboard(): void
    {
        // 1. Crear roles requeridos
        $rolCliente = Rol::create([
            'id_rol' => 2,
            'nombre' => 'Cliente',
            'descripcion' => 'Rol de comprador',
        ]);

        // 2. Crear usuario cliente de prueba
        $cliente = Usuario::create([
            'id_rol' => $rolCliente->id_rol,
            'nombre' => 'Michi',
            'apellido' => 'Kawaii',
            'email' => 'michi@junosstore.local',
            'password_hash' => Hash::make('password123'),
            'activo' => 1,
        ]);

        // 3. Crear una dirección de prueba activa
        Direccion::create([
            'id_usuario' => $cliente->id_usuario,
            'calle' => 'Calle 100 # 15 - 20',
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca',
            'principal' => true,
            'activo' => true,
        ]);

        // 4. Actuar como el cliente autenticado
        $response = $this->actingAs($cliente)->get('/mi-cuenta');

        $response->assertStatus(200);
        $response->assertSee('Michi');
        $response->assertSee('Mi Espacio');
        $response->assertSee('Calle 100 # 15 - 20');
    }

    /**
     * Un cliente puede actualizar su información de contacto.
     */
    public function test_client_can_update_profile_information(): void
    {
        $rolCliente = Rol::create([
            'id_rol' => 2,
            'nombre' => 'Cliente',
            'descripcion' => 'Rol de comprador',
        ]);

        $cliente = Usuario::create([
            'id_rol' => $rolCliente->id_rol,
            'nombre' => 'Luna',
            'apellido' => 'Pastel',
            'email' => 'luna@junosstore.local',
            'password_hash' => Hash::make('password123'),
            'telefono' => '3001112233',
            'activo' => 1,
        ]);

        $response = $this->actingAs($cliente)->post('/cliente/perfil', [
            'nombre' => 'Luna Modificada',
            'apellido' => 'Pastelito',
            'telefono' => '3119998877',
        ]);

        $response->assertRedirect(route('cliente.dashboard', ['tab' => 'perfil']));

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $cliente->id_usuario,
            'nombre' => 'Luna Modificada',
            'telefono' => '3119998877',
        ]);
    }
}
