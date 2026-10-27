<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ==============================================================================
 * MIGRACIÓN: Catálogo de Productos (Juno's Store)
 * ==============================================================================
 * Define las tablas relacionales para el catálogo comercial:
 * - categorias: Familias principales
 * - subcategorias: Clasificación secundaria
 * - marcas: Fabricantes / Marcas
 * - productos: Artículos maestros a la venta
 * - producto_imagenes: Galería fotográfica con selector de imagen principal
 * ==============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla: categorias
        if (! Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->increments('id_categoria');
                $table->string('nombre', 100)->unique();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();
            });
        }

        // 2. Tabla: subcategorias
        if (! Schema::hasTable('subcategorias')) {
            Schema::create('subcategorias', function (Blueprint $table) {
                $table->increments('id_subcategoria');
                $table->unsignedInteger('id_categoria');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();

                $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // 3. Tabla: marcas
        if (! Schema::hasTable('marcas')) {
            Schema::create('marcas', function (Blueprint $table) {
                $table->increments('id_marca');
                $table->string('nombre', 100)->unique();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
            });
        }

        // 4. Tabla: productos
        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->increments('id_producto');
                $table->unsignedInteger('id_categoria');
                $table->unsignedInteger('id_subcategoria')->nullable();
                $table->unsignedInteger('id_marca')->nullable();
                $table->string('sku', 60)->nullable()->unique();
                $table->string('nombre', 180);
                $table->text('descripcion')->nullable();
                $table->decimal('precio', 12, 2)->default(0.00);
                $table->integer('stock_actual')->default(0);
                $table->integer('stock_minimo')->default(5);
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();

                $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onUpdate('cascade')->onDelete('restrict');
                $table->foreign('id_subcategoria')->references('id_subcategoria')->on('subcategorias')->onUpdate('cascade')->onDelete('set null');
                $table->foreign('id_marca')->references('id_marca')->on('marcas')->onUpdate('cascade')->onDelete('set null');
            });
        }

        // 5. Tabla: producto_imagenes
        if (! Schema::hasTable('producto_imagenes')) {
            Schema::create('producto_imagenes', function (Blueprint $table) {
                $table->increments('id_imagen');
                $table->unsignedInteger('id_producto');
                $table->string('ruta_imagen', 255)->nullable();
                $table->string('url_imagen', 255)->nullable();
                $table->boolean('es_principal')->default(false);
                $table->boolean('principal')->default(false);
                $table->integer('orden')->default(1);
                $table->integer('orden_imagen')->default(1);
                $table->string('texto_alternativo', 180)->nullable();

                $table->foreign('id_producto')->references('id_producto')->on('productos')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_imagenes');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('marcas');
        Schema::dropIfExists('subcategorias');
        Schema::dropIfExists('categorias');
    }
};
