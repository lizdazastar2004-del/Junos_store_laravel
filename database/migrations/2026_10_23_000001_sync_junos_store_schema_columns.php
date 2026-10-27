<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ==============================================================================
 * MIGRACIÓN DE SINCRONIZACIÓN Y COMPATIBILIDAD (Juno's Store)
 * ==============================================================================
 * Garantiza la existencia de columnas y tablas complementarias requeridas por
 * los modelos y controladores de Laravel sin afectar los registros existentes.
 * Permite que tanto MySQL como SQLite (en pruebas) mantengan idéntica paridad.
 * ==============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla: codigos_descuento (utilizada por CodigoDescuento Model y CarritoController)
        if (! Schema::hasTable('codigos_descuento')) {
            Schema::create('codigos_descuento', function (Blueprint $table) {
                $table->increments('id_codigo_descuento');
                $table->string('codigo', 50)->unique();
                $table->enum('tipo', ['porcentaje', 'fijo'])->default('porcentaje');
                $table->decimal('valor', 12, 2)->default(0.00);
                $table->decimal('monto_minimo', 12, 2)->default(0.00);
                $table->dateTime('fecha_inicio')->nullable();
                $table->dateTime('fecha_fin')->nullable();
                $table->integer('usos_maximos')->default(100);
                $table->integer('usos_actuales')->default(0);
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();
            });
        }

        // 2. Columnas en: marcas
        if (Schema::hasTable('marcas')) {
            Schema::table('marcas', function (Blueprint $table) {
                if (! Schema::hasColumn('marcas', 'descripcion')) {
                    $table->text('descripcion')->nullable();
                }
            });
        }

        // 3. Columnas en: metodos_pago
        if (Schema::hasTable('metodos_pago')) {
            Schema::table('metodos_pago', function (Blueprint $table) {
                if (! Schema::hasColumn('metodos_pago', 'descripcion')) {
                    $table->string('descripcion', 255)->nullable();
                }
            });
        }

        // 4. Columnas en: carritos
        if (Schema::hasTable('carritos')) {
            Schema::table('carritos', function (Blueprint $table) {
                if (! Schema::hasColumn('carritos', 'codigo_descuento_id')) {
                    $table->unsignedInteger('codigo_descuento_id')->nullable();
                }
                if (! Schema::hasColumn('carritos', 'descuento_aplicado')) {
                    $table->decimal('descuento_aplicado', 12, 2)->default(0.00);
                }
            });
        }

        // 5. Columnas en: pedidos
        if (Schema::hasTable('pedidos')) {
            Schema::table('pedidos', function (Blueprint $table) {
                if (! Schema::hasColumn('pedidos', 'numero_pedido')) {
                    $table->string('numero_pedido', 30)->nullable();
                }
                if (! Schema::hasColumn('pedidos', 'id_direccion')) {
                    $table->unsignedBigInteger('id_direccion')->nullable();
                }
                if (! Schema::hasColumn('pedidos', 'observaciones')) {
                    $table->string('observaciones', 500)->nullable();
                }
                if (! Schema::hasColumn('pedidos', 'fecha_pedido')) {
                    $table->dateTime('fecha_pedido')->nullable();
                }
                if (! Schema::hasColumn('pedidos', 'codigo_descuento_id')) {
                    $table->unsignedInteger('codigo_descuento_id')->nullable();
                }
                if (! Schema::hasColumn('pedidos', 'direccion_entrega')) {
                    $table->text('direccion_entrega')->nullable();
                }
                if (! Schema::hasColumn('pedidos', 'notas')) {
                    $table->text('notas')->nullable();
                }
                if (! Schema::hasColumn('pedidos', 'creado_en')) {
                    $table->dateTime('creado_en')->nullable()->useCurrent();
                }
            });
        }

        // 6. Columnas en: pedido_detalles
        if (Schema::hasTable('pedido_detalles')) {
            Schema::table('pedido_detalles', function (Blueprint $table) {
                if (! Schema::hasColumn('pedido_detalles', 'nombre_producto')) {
                    $table->string('nombre_producto', 180)->nullable();
                }
            });
        }

        // 7. Columnas en: pagos
        if (Schema::hasTable('pagos')) {
            Schema::table('pagos', function (Blueprint $table) {
                if (! Schema::hasColumn('pagos', 'referencia')) {
                    $table->string('referencia', 100)->nullable();
                }
                if (! Schema::hasColumn('pagos', 'fecha_transaccion')) {
                    $table->dateTime('fecha_transaccion')->nullable();
                }
                if (! Schema::hasColumn('pagos', 'observaciones')) {
                    $table->string('observaciones', 500)->nullable();
                }
                if (! Schema::hasColumn('pagos', 'fecha_pago')) {
                    $table->dateTime('fecha_pago')->nullable();
                }
                if (! Schema::hasColumn('pagos', 'referencia_externa')) {
                    $table->string('referencia_externa', 100)->nullable();
                }
                if (! Schema::hasColumn('pagos', 'datos_adicionales')) {
                    $table->text('datos_adicionales')->nullable();
                }
                if (! Schema::hasColumn('pagos', 'creado_en')) {
                    $table->dateTime('creado_en')->nullable()->useCurrent();
                }
            });
        }

        // 8. Columnas en: resenas
        if (Schema::hasTable('resenas')) {
            Schema::table('resenas', function (Blueprint $table) {
                if (! Schema::hasColumn('resenas', 'id_pedido')) {
                    $table->unsignedBigInteger('id_pedido')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Se preserva la integridad de datos existentes
    }
};
