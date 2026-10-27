<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ==============================================================================
 * TEST: ExampleTest
 * ==============================================================================
 * Verifica la respuesta HTTP 200 de la página principal del catálogo de la tienda.
 * Utiliza RefreshDatabase para migrar las tablas en la base de datos de pruebas.
 * ==============================================================================
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba que la página de inicio responda exitosamente (código HTTP 200).
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
