<?php

/**
 * Configuración de Constantes de la Aplicación
 * Juno's Store - Laravel
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de Envíos
    |--------------------------------------------------------------------------
    */

    // Costo de envío estándar en COP
    'SHIPPING_COST' => 8000,

    // Monto mínimo para envío gratis en COP
    'SHIPPING_FREE_MIN' => 150000,

    /*
    |--------------------------------------------------------------------------
    | Configuración de Productos
    |--------------------------------------------------------------------------
    */

    // Número de productos por página en el catálogo
    'PRODUCTS_PER_PAGE' => 12,

    // Número máximo de imágenes por producto
    'MAX_PRODUCT_IMAGES' => 5,

    /*
    |--------------------------------------------------------------------------
    | Configuración de Pedidos
    |--------------------------------------------------------------------------
    */

    // Estados de pedido disponibles
    'ESTADOS_PEDIDO' => [
        'pendiente' => 'Pendiente',
        'confirmado' => 'Confirmado',
        'en_preparacion' => 'En Preparación',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ],

    // Estados de pago disponibles
    'ESTADOS_PAGO' => [
        'pendiente' => 'Pendiente',
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
        'reembolsado' => 'Reembolsado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Archivos
    |--------------------------------------------------------------------------
    */

    // Tamaño máximo de imagen en bytes (5MB)
    'MAX_IMAGE_SIZE' => 5242880,

    // Extensiones de imagen permitidas
    'ALLOWED_IMAGE_EXTENSIONS' => ['jpg', 'jpeg', 'png', 'webp'],

];
