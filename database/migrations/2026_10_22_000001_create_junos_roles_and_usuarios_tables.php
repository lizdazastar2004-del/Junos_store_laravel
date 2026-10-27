<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ==============================================================================
 * MIGRACIÓN: Roles y Usuarios (Juno's Store)
 * ==============================================================================
 * Define las tablas fundamentales para la autenticación y control de acceso RBAC.
 * Incluye comprobación Schema::hasTable para compatibilidad con bases de datos
 * existentes en MySQL y nuevos entornos de testing en SQLite.
 * ==============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla: roles
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id_rol');
                $table->string('nombre', 50)->unique();
                $table->string('descripcion', 255)->nullable();
                $table->dateTime('creado_en')->nullable()->useCurrent();
            });
        }

        // 2. Tabla: usuarios
        if (! Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->increments('id_usuario');
                $table->unsignedInteger('id_rol')->default(2);
                $table->string('nombre', 80);
                $table->string('apellido', 80);
                $table->string('email', 150)->unique();
                $table->string('password_hash', 255);
                $table->string('telefono', 30)->nullable();
                $table->boolean('activo')->default(true);
                $table->boolean('email_verificado')->default(false);
                $table->unsignedSmallInteger('intentos_fallidos')->default(0);
                $table->dateTime('bloqueado_hasta')->nullable();
                $table->dateTime('ultimo_acceso')->nullable();
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();
                $table->rememberToken();
                $table->string('token_activacion', 100)->nullable();
                $table->dateTime('token_expira_en')->nullable();

                // Llave foránea hacia 'roles'
                $table->foreign('id_rol')->references('id_rol')->on('roles')->onUpdate('cascade')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
