<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ==============================================================================
 * MIGRACIÓN: Pedidos, Pagos, Carritos y Soporte Comercial (Juno's Store)
 * ==============================================================================
 * Define el circuito transaccional del e-commerce:
 * - direcciones: Libreta de envío de los clientes
 * - metodos_pago: Opciones habilitadas
 * - descuentos: Códigos y cupones promocionales
 * - carritos y carrito_detalles: Cesta de compra persistente
 * - pedidos y pedido_detalles: Órdenes de compra generadas
 * - pagos: Transacciones financieras y comprobantes
 * - movimientos_inventario: Trazabilidad kardex de stock (entradas / salidas)
 * - notificaciones: Alertas a usuarios y administradores
 * - resenas: Calificaciones y opiniones de clientes
 * - proveedores: Registro de proveedores comerciales
 * - facturas: Comprobantes contables
 * ==============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla: direcciones
        if (! Schema::hasTable('direcciones')) {
            Schema::create('direcciones', function (Blueprint $table) {
                $table->increments('id_direccion');
                $table->unsignedInteger('id_usuario');
                $table->string('calle', 180);
                $table->string('ciudad', 80);
                $table->string('departamento', 80);
                $table->string('codigo_postal', 10)->nullable();
                $table->string('referencia', 255)->nullable();
                $table->boolean('principal')->default(false);
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // 2. Tabla: metodos_pago
        if (! Schema::hasTable('metodos_pago')) {
            Schema::create('metodos_pago', function (Blueprint $table) {
                $table->increments('id_metodo_pago');
                $table->string('nombre', 60);
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // 3. Tabla: descuentos
        if (! Schema::hasTable('descuentos')) {
            Schema::create('descuentos', function (Blueprint $table) {
                $table->increments('id_descuento');
                $table->string('codigo', 50)->unique();
                $table->decimal('porcentaje', 5, 2)->nullable();
                $table->decimal('valor_fijo', 12, 2)->nullable();
                $table->decimal('compra_minima', 12, 2)->default(0);
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->integer('usos_maximos')->nullable();
                $table->integer('usos_actuales')->default(0);
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
            });
        }

        // 4. Tabla: carritos
        if (! Schema::hasTable('carritos')) {
            Schema::create('carritos', function (Blueprint $table) {
                $table->increments('id_carrito');
                $table->unsignedInteger('id_usuario');
                $table->string('estado', 20)->default('ACTIVO');
                $table->unsignedInteger('codigo_descuento_id')->nullable();
                $table->decimal('descuento_aplicado', 12, 2)->default(0.00);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // 5. Tabla: carrito_detalles
        if (! Schema::hasTable('carrito_detalles')) {
            Schema::create('carrito_detalles', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_carrito');
                $table->unsignedInteger('id_producto');
                $table->integer('cantidad')->default(1);
                $table->decimal('precio_unitario', 12, 2);
                $table->dateTime('creado_en')->nullable()->useCurrent();

                $table->foreign('id_carrito')->references('id_carrito')->on('carritos')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('id_producto')->references('id_producto')->on('productos')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // 6. Tabla: pedidos
        if (! Schema::hasTable('pedidos')) {
            Schema::create('pedidos', function (Blueprint $table) {
                $table->increments('id_pedido');
                $table->unsignedInteger('id_usuario');
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->decimal('descuento', 12, 2)->default(0.00);
                $table->decimal('costo_envio', 12, 2)->default(0.00);
                $table->decimal('total', 12, 2)->default(0.00);
                $table->string('estado', 30)->default('pendiente');
                $table->text('direccion_entrega');
                $table->text('notas')->nullable();
                $table->unsignedInteger('codigo_descuento_id')->nullable();
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onUpdate('cascade')->onDelete('restrict');
            });
        }

        // 7. Tabla: pedido_detalles
        if (! Schema::hasTable('pedido_detalles')) {
            Schema::create('pedido_detalles', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_pedido');
                $table->unsignedInteger('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 12, 2);
                $table->decimal('subtotal', 12, 2);

                $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('id_producto')->references('id_producto')->on('productos')->onUpdate('cascade')->onDelete('restrict');
            });
        }

        // 8. Tabla: pagos
        if (! Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table) {
                $table->increments('id_pago');
                $table->unsignedInteger('id_pedido');
                $table->unsignedInteger('id_metodo_pago');
                $table->decimal('monto', 12, 2);
                $table->string('estado', 30)->default('pendiente');
                $table->string('referencia_externa', 100)->nullable();
                $table->text('datos_adicionales')->nullable();
                $table->dateTime('fecha_pago')->nullable()->useCurrent();

                $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('id_metodo_pago')->references('id_metodo_pago')->on('metodos_pago')->onUpdate('cascade')->onDelete('restrict');
            });
        }

        // 9. Tabla: movimientos_inventario (Kardex)
        if (! Schema::hasTable('movimientos_inventario')) {
            Schema::create('movimientos_inventario', function (Blueprint $table) {
                $table->increments('id_movimiento');
                $table->unsignedInteger('id_producto');
                $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste']);
                $table->integer('cantidad');
                $table->string('motivo', 255)->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->dateTime('creado_en')->nullable()->useCurrent();

                $table->foreign('id_producto')->references('id_producto')->on('productos')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onUpdate('cascade')->onDelete('set null');
            });
        }

        // 10. Tabla: notificaciones
        if (! Schema::hasTable('notificaciones')) {
            Schema::create('notificaciones', function (Blueprint $table) {
                $table->increments('id_notificacion');
                $table->unsignedInteger('id_usuario');
                $table->string('tipo', 50);
                $table->string('titulo', 150);
                $table->text('mensaje');
                $table->boolean('leida')->default(false);
                $table->dateTime('creado_en')->nullable()->useCurrent();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // 11. Tabla: resenas
        if (! Schema::hasTable('resenas')) {
            Schema::create('resenas', function (Blueprint $table) {
                $table->increments('id_resena');
                $table->unsignedInteger('id_usuario');
                $table->unsignedInteger('id_producto');
                $table->unsignedInteger('id_pedido')->nullable();
                $table->unsignedTinyInteger('calificacion')->default(5);
                $table->text('comentario')->nullable();
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();

                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('id_producto')->references('id_producto')->on('productos')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // 12. Tabla: proveedores
        if (! Schema::hasTable('proveedores')) {
            Schema::create('proveedores', function (Blueprint $table) {
                $table->increments('id_proveedor');
                $table->string('nombre', 120);
                $table->string('contacto', 100)->nullable();
                $table->string('telefono', 40)->nullable();
                $table->string('email', 150)->nullable();
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->nullable()->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrent();
            });
        }

        // 13. Tabla: facturas
        if (! Schema::hasTable('facturas')) {
            Schema::create('facturas', function (Blueprint $table) {
                $table->increments('id_factura');
                $table->unsignedInteger('id_pedido');
                $table->string('numero_factura', 60)->unique();
                $table->decimal('subtotal', 12, 2);
                $table->decimal('descuento', 12, 2)->default(0.00);
                $table->decimal('impuestos', 12, 2)->default(0.00);
                $table->decimal('total', 12, 2);
                $table->dateTime('fecha_emision')->nullable()->useCurrent();
                $table->string('url_pdf', 255)->nullable();
                $table->boolean('enviada_email')->default(false);

                $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('resenas');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('pedido_detalles');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('carrito_detalles');
        Schema::dropIfExists('carritos');
        Schema::dropIfExists('descuentos');
        Schema::dropIfExists('metodos_pago');
        Schema::dropIfExists('direcciones');
    }
};
