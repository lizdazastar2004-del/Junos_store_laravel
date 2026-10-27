<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\CodigoDescuento;
use App\Models\Direccion;
use App\Models\Marca;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\Resena;
use App\Models\Rol;
use App\Models\Subcategoria;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * ==============================================================================
 * SEEDER: DemoDataSeeder
 * ==============================================================================
 * Genera datos de prueba ricos, coherentes y estéticos para Juno's Store.
 *
 * Utilidad para el Administrador y la Evaluación:
 * 1. Permite probar el Dashboard con métricas reales (KPIs de ventas y stock).
 * 2. Genera productos en todos los estados (en stock, stock bajo crítico y agotados).
 * 3. Habilita cupones promocionales ('MIAU10', 'JUNO15K', 'PASTEL20') para probar el carrito.
 * 4. Crea pedidos en todos los estados (pendiente, confirmado, en preparación, enviado, entregado).
 * 5. Genera usuarios de prueba (Admin y Clientes) con contraseñas conocidas.
 * ==============================================================================
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. ROLES DEL SISTEMA ─────────────────────────────────────
        $rolAdmin = Rol::updateOrCreate(
            ['id_rol' => 1],
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Control total del inventario, pedidos, usuarios y reportes',
                'creado_en' => now(),
            ]
        );

        $rolCliente = Rol::updateOrCreate(
            ['id_rol' => 2],
            [
                'nombre' => 'Cliente',
                'descripcion' => 'Usuario comprador, navega catálogo, compra y gestiona pedidos',
                'creado_en' => now(),
            ]
        );

        // ── 2. USUARIOS DE PRUEBA ────────────────────────────────────
        // Administrador Principal
        $admin = Usuario::updateOrCreate(
            ['email' => 'admin@junosstore.local'],
            [
                'id_rol' => 1,
                'nombre' => 'Juno',
                'apellido' => 'Admin',
                'password_hash' => Hash::make('admin123'),
                'telefono' => '3001234567',
                'activo' => 1,
                'email_verificado' => 1,
                'ultimo_acceso' => now(),
            ]
        );

        // Cliente Demo 1
        $cliente1 = Usuario::updateOrCreate(
            ['email' => 'cliente@junosstore.local'],
            [
                'id_rol' => 2,
                'nombre' => 'Lizbeth',
                'apellido' => 'Daza',
                'password_hash' => Hash::make('cliente123'),
                'telefono' => '3109876543',
                'activo' => 1,
                'email_verificado' => 1,
                'ultimo_acceso' => now(),
            ]
        );

        // Cliente Demo 2
        $cliente2 = Usuario::updateOrCreate(
            ['email' => 'karen@junosstore.local'],
            [
                'id_rol' => 2,
                'nombre' => 'Karen',
                'apellido' => 'Gómez',
                'password_hash' => Hash::make('cliente123'),
                'telefono' => '3205557788',
                'activo' => 1,
                'email_verificado' => 1,
            ]
        );

        // ── 3. MARCAS COMERCIALES ────────────────────────────────────
        $marcasData = [
            ['nombre' => 'Juno\'s Studio', 'descripcion' => 'Diseños originales y tiernos exclusivos'],
            ['nombre' => 'Sanrio Kawaii', 'descripcion' => 'Artículos licenciados y coleccionables'],
            ['nombre' => 'Pusheen Cat', 'descripcion' => 'Accesorios oficiales del gatito más famoso'],
            ['nombre' => 'Neko Stationery', 'descripcion' => 'Papelería coreana y japonesa de alta calidad'],
            ['nombre' => 'Pastel Planet', 'descripcion' => 'Accesorios y peluches en tonos pastel'],
        ];

        $marcas = [];
        foreach ($marcasData as $m) {
            $marcas[$m['nombre']] = Marca::updateOrCreate(['nombre' => $m['nombre']], [
                'descripcion' => $m['descripcion'],
                'activo' => 1,
                'creado_en' => now(),
            ]);
        }

        // ── 4. CATEGORÍAS Y SUBCATEGORÍAS ────────────────────────────
        $catPapeleria = Categoria::updateOrCreate(
            ['id_categoria' => 1],
            [
                'nombre' => 'Papelería & Escritorio',
                'descripcion' => 'Cuadernos, libretas, lapiceros, cintas washi y todo para organizar tus notas con ternura.',
                'activo' => 1,
            ]
        );

        $catPeluches = Categoria::updateOrCreate(
            ['id_categoria' => 2],
            [
                'nombre' => 'Peluches & Coleccionables',
                'descripcion' => 'Gatitos de felpa ultrasuaves, llaveros pastel, figuras y accesorios tiernos.',
                'activo' => 1,
            ]
        );

        $catHogar = Categoria::updateOrCreate(
            ['id_categoria' => 3],
            [
                'nombre' => 'Moda & Hogar Kawaii',
                'descripcion' => 'Tazas térmicas, mochilas, organizadores y detalles que alegran tus espacios.',
                'activo' => 1,
            ]
        );

        // Subcategorías
        $subLibretas = Subcategoria::updateOrCreate(['nombre' => 'Cuadernos y Libretas'], [
            'id_categoria' => $catPapeleria->id_categoria,
            'descripcion' => 'Hojas punteadas y rayadas de alto gramaje',
            'activo' => 1,
        ]);

        $subLapiceros = Subcategoria::updateOrCreate(['nombre' => 'Lapiceros y Marcadores'], [
            'id_categoria' => $catPapeleria->id_categoria,
            'descripcion' => 'Tinta gel, punta fina y resaltadores pastel',
            'activo' => 1,
        ]);

        $subWashi = Subcategoria::updateOrCreate(['nombre' => 'Stickers y Washi Tapes'], [
            'id_categoria' => $catPapeleria->id_categoria,
            'descripcion' => 'Cintas decorativas y pegatinas holográficas',
            'activo' => 1,
        ]);

        $subPeluchesGato = Subcategoria::updateOrCreate(['nombre' => 'Peluches de Gato'], [
            'id_categoria' => $catPeluches->id_categoria,
            'descripcion' => 'Gatitos dormilones y suaves para abrazar',
            'activo' => 1,
        ]);

        $subLlaveros = Subcategoria::updateOrCreate(['nombre' => 'Llaveros y Accesorios'], [
            'id_categoria' => $catPeluches->id_categoria,
            'descripcion' => 'Llaveros acrílicos y correas para celular',
            'activo' => 1,
        ]);

        $subTazas = Subcategoria::updateOrCreate(['nombre' => 'Tazas y Botellas'], [
            'id_categoria' => $catHogar->id_categoria,
            'descripcion' => 'Tazas con tapa de orejitas y termos térmicos',
            'activo' => 1,
        ]);

        // ── 5. PRODUCTOS DE PRUEBA EN DIFERENTES ESTADOS ─────────────
        $productosData = [
            // 1. Producto con Stock Normal
            [
                'nombre' => 'Libreta Gatito Pastel A5 Punteada',
                'descripcion' => 'Libreta con pasta dura holográfica, 160 páginas punteadas de 100g, cinta separadora y bolsillo interior para stickers.',
                'precio' => 32000.00,
                'id_categoria' => $catPapeleria->id_categoria,
                'id_subcategoria' => $subLibretas->id_subcategoria,
                'id_marca' => $marcas['Juno\'s Studio']->id_marca,
                'sku' => 'JUNO-100001',
                'stock_actual' => 24,
                'stock_minimo' => 5,
                'activo' => 1,
                'svg_icon' => '📓',
                'svg_bg1' => '#ded5f5',
                'svg_bg2' => '#c4b5e0',
                'svg_tag' => 'Libretas',
            ],
            // 2. Producto con Stock Normal
            [
                'nombre' => 'Set de 6 Resaltadores Pastel Soft Glow',
                'descripcion' => 'Set de resaltadores punta biselada en tonos lavanda, menta, durazno, rosa suave, amarillo y celeste pastel. Secado ultra rápido.',
                'precio' => 18500.00,
                'id_categoria' => $catPapeleria->id_categoria,
                'id_subcategoria' => $subLapiceros->id_subcategoria,
                'id_marca' => $marcas['Neko Stationery']->id_marca,
                'sku' => 'JUNO-100002',
                'stock_actual' => 35,
                'stock_minimo' => 8,
                'activo' => 1,
                'svg_icon' => '🖍️',
                'svg_bg1' => '#ffeaa7',
                'svg_bg2' => '#fdcb6e',
                'svg_tag' => 'Escritura',
            ],
            // 3. Producto con Stock Bajo (Alerta para el Administrador)
            [
                'nombre' => 'Peluche Gatito Mochi Dormilón 30cm',
                'descripcion' => 'Peluche extra elástico relleno de algodón siliconado hipoalergénico. Ideal para decorar tu cama o escritorio.',
                'precio' => 58000.00,
                'id_categoria' => $catPeluches->id_categoria,
                'id_subcategoria' => $subPeluchesGato->id_subcategoria,
                'id_marca' => $marcas['Pusheen Cat']->id_marca,
                'sku' => 'JUNO-100003',
                'stock_actual' => 3, // STOCK CRÍTICO <= stock_minimo
                'stock_minimo' => 6,
                'activo' => 1,
                'svg_icon' => '🐱',
                'svg_bg1' => '#ffd2dc',
                'svg_bg2' => '#fca1b7',
                'svg_tag' => 'Peluches',
            ],
            // 4. Producto Agotado (Para probar reabastecimiento en Dashboard)
            [
                'nombre' => 'Taza Cerámica Gatito con Cuchara Huellita',
                'descripcion' => 'Taza de 380ml con relieve 3D de carita de gato, tapa protectora y cuchara dorada con huella de felino.',
                'precio' => 42000.00,
                'id_categoria' => $catHogar->id_categoria,
                'id_subcategoria' => $subTazas->id_subcategoria,
                'id_marca' => $marcas['Pastel Planet']->id_marca,
                'sku' => 'JUNO-100004',
                'stock_actual' => 0, // AGOTADO
                'stock_minimo' => 5,
                'activo' => 1,
                'svg_icon' => '☕',
                'svg_bg1' => '#d4f1f4',
                'svg_bg2' => '#a3e4d7',
                'svg_tag' => 'Hogar',
            ],
            // 5. Producto con Stock Normal
            [
                'nombre' => 'Caja de 4 Cintas Washi Tape Flores y Nubes',
                'descripcion' => 'Cuatro rollos de cinta adhesiva de papel de arroz japonés con foil dorado y estampado botánico pastel.',
                'precio' => 15000.00,
                'id_categoria' => $catPapeleria->id_categoria,
                'id_subcategoria' => $subWashi->id_subcategoria,
                'id_marca' => $marcas['Juno\'s Studio']->id_marca,
                'sku' => 'JUNO-100005',
                'stock_actual' => 18,
                'stock_minimo' => 4,
                'activo' => 1,
                'svg_icon' => '🎀',
                'svg_bg1' => '#e8daf8',
                'svg_bg2' => '#d2b4de',
                'svg_tag' => 'Deco',
            ],
            // 6. Producto con Stock Normal
            [
                'nombre' => 'Llavero Acrílico Shaker Estrellitas Líquidas',
                'descripcion' => 'Llavero con purpurina líquida y dijes flotantes de estrellas y patitas de gato. Broche metálico en forma de corazón.',
                'precio' => 12500.00,
                'id_categoria' => $catPeluches->id_categoria,
                'id_subcategoria' => $subLlaveros->id_subcategoria,
                'id_marca' => $marcas['Sanrio Kawaii']->id_marca,
                'sku' => 'JUNO-100006',
                'stock_actual' => 40,
                'stock_minimo' => 10,
                'activo' => 1,
                'svg_icon' => '⭐',
                'svg_bg1' => '#fef5d1',
                'svg_bg2' => '#f9e79f',
                'svg_tag' => 'Accesorios',
            ],
        ];

        $productosCreados = [];
        foreach ($productosData as $pData) {
            $svgIcon = $pData['svg_icon'];
            $svgBg1 = $pData['svg_bg1'];
            $svgBg2 = $pData['svg_bg2'];
            $svgTag = $pData['svg_tag'];

            unset($pData['svg_icon'], $pData['svg_bg1'], $pData['svg_bg2'], $pData['svg_tag']);

            $producto = Producto::updateOrCreate(['sku' => $pData['sku']], $pData);
            $productosCreados[] = $producto;

            // Generar archivo SVG físico y registro en producto_imagenes
            $dirProd = public_path("img/productos/{$producto->id_producto}");
            if (! File::exists($dirProd)) {
                File::makeDirectory($dirProd, 0755, true);
            }

            $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="500" height="500">
  <defs>
    <linearGradient id="grad{$producto->id_producto}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$svgBg1}"/>
      <stop offset="100%" stop-color="{$svgBg2}"/>
    </linearGradient>
    <filter id="shadow{$producto->id_producto}" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="12" stdDeviation="14" flood-color="#4a2d7a" flood-opacity="0.14"/>
    </filter>
  </defs>
  <rect width="500" height="500" fill="url(#grad{$producto->id_producto})"/>
  <circle cx="70" cy="70" r="10" fill="#ffffff" opacity="0.6"/>
  <circle cx="430" cy="80" r="14" fill="#ffffff" opacity="0.5"/>
  <circle cx="420" cy="420" r="12" fill="#ffffff" opacity="0.5"/>
  <circle cx="80" cy="430" r="10" fill="#ffffff" opacity="0.6"/>
  <text x="60" y="160" font-size="28" fill="#ffffff" opacity="0.7">✨</text>
  <text x="400" y="240" font-size="32" fill="#ffffff" opacity="0.7">🌸</text>
  <g filter="url(#shadow{$producto->id_producto})">
    <rect x="75" y="75" width="350" height="350" rx="40" fill="#ffffff" opacity="0.95"/>
    <rect x="75" y="75" width="350" height="350" rx="40" fill="none" stroke="#ede4f8" stroke-width="3"/>
  </g>
  <rect x="150" y="105" width="200" height="32" rx="16" fill="#f4eafc"/>
  <text x="250" y="127" font-family="'Nunito', sans-serif" font-size="13" font-weight="800" fill="#7a4adc" text-anchor="middle">{$svgTag}</text>
  <text x="250" y="260" font-size="110" text-anchor="middle" dominant-baseline="middle">{$svgIcon}</text>
  <text x="250" y="345" font-family="'Nunito', sans-serif" font-size="18" font-weight="900" fill="#352b43" text-anchor="middle">Juno's Store</text>
  <text x="250" y="375" font-family="'Nunito', sans-serif" font-size="13" font-weight="700" fill="#88729f" text-anchor="middle">Colección Pastel</text>
</svg>
SVG;

            $archivoSvg = "producto_{$producto->id_producto}.svg";
            File::put("{$dirProd}/{$archivoSvg}", $svgContent);

            ProductoImagen::updateOrCreate(
                [
                    'id_producto' => $producto->id_producto,
                    'principal' => true,
                ],
                [
                    'url_imagen' => "/img/productos/{$producto->id_producto}/{$archivoSvg}",
                    'orden_imagen' => 1,
                    'texto_alternativo' => $producto->nombre,
                ]
            );
        }

        // ── 6. CUPONES DE DESCUENTO ──────────────────────────────────
        CodigoDescuento::updateOrCreate(['codigo' => 'MIAU10'], [
            'tipo' => 'porcentaje',
            'valor' => 10.00,
            'monto_minimo' => 20000.00,
            'fecha_inicio' => now()->subDays(30),
            'fecha_fin' => now()->addDays(60),
            'usos_maximos' => 100,
            'usos_actuales' => 5,
            'activo' => 1,
        ]);

        CodigoDescuento::updateOrCreate(['codigo' => 'JUNO15K'], [
            'tipo' => 'fijo',
            'valor' => 15000.00,
            'monto_minimo' => 70000.00,
            'fecha_inicio' => now()->subDays(30),
            'fecha_fin' => now()->addDays(60),
            'usos_maximos' => 50,
            'usos_actuales' => 12,
            'activo' => 1,
        ]);

        CodigoDescuento::updateOrCreate(['codigo' => 'PASTEL20'], [
            'tipo' => 'porcentaje',
            'valor' => 20.00,
            'monto_minimo' => 90000.00,
            'fecha_inicio' => now()->subDays(30),
            'fecha_fin' => now()->addDays(60),
            'usos_maximos' => 200,
            'usos_actuales' => 2,
            'activo' => 1,
        ]);

        if (Schema::hasTable('descuentos')) {
            DB::table('descuentos')->updateOrInsert(['codigo' => 'MIAU10'], [
                'porcentaje' => 10.00,
                'compra_minima' => 20000.00,
                'fecha_inicio' => now()->subDays(30),
                'fecha_fin' => now()->addDays(60),
                'usos_maximos' => 100,
                'usos_actuales' => 5,
                'activo' => 1,
            ]);
            DB::table('descuentos')->updateOrInsert(['codigo' => 'JUNO15K'], [
                'valor_fijo' => 15000.00,
                'compra_minima' => 70000.00,
                'fecha_inicio' => now()->subDays(30),
                'fecha_fin' => now()->addDays(60),
                'usos_maximos' => 50,
                'usos_actuales' => 12,
                'activo' => 1,
            ]);
            DB::table('descuentos')->updateOrInsert(['codigo' => 'PASTEL20'], [
                'porcentaje' => 20.00,
                'compra_minima' => 90000.00,
                'fecha_inicio' => now()->subDays(30),
                'fecha_fin' => now()->addDays(60),
                'usos_maximos' => 200,
                'usos_actuales' => 2,
                'activo' => 1,
            ]);
        }

        // ── 7. DIRECCIONES DE PRUEBA ─────────────────────────────────
        $dir1 = Direccion::firstOrCreate(
            ['id_usuario' => $cliente1->id_usuario, 'calle' => 'Carrera 15 # 85 - 24 Apto 402'],
            [
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'codigo_postal' => '110221',
                'referencia' => 'Edificio blanco con portería 24h',
                'principal' => 1,
                'activo' => 1,
            ]
        );

        $dir2 = Direccion::firstOrCreate(
            ['id_usuario' => $cliente2->id_usuario, 'calle' => 'Calle 48 # 32 - 15'],
            [
                'ciudad' => 'Medellín',
                'departamento' => 'Antioquia',
                'codigo_postal' => '050012',
                'referencia' => 'Cerca al parque principal',
                'principal' => 1,
                'activo' => 1,
            ]
        );

        // ── 8. MÉTODOS DE PAGO ───────────────────────────────────────
        $metodoTransf = MetodoPago::updateOrCreate(['id_metodo_pago' => 1], [
            'nombre' => 'Transferencia',
            'descripcion' => 'Transferencia Bancaria, Nequi o Daviplata',
            'activo' => 1,
        ]);

        $metodoPse = MetodoPago::updateOrCreate(['id_metodo_pago' => 2], [
            'nombre' => 'Efectivo',
            'descripcion' => 'Pago en efectivo o contra entrega',
            'activo' => 1,
        ]);

        $metodoTarjeta = MetodoPago::updateOrCreate(['id_metodo_pago' => 3], [
            'nombre' => 'Tarjeta',
            'descripcion' => 'Tarjeta de Crédito o Débito',
            'activo' => 1,
        ]);

        // ── 9. PEDIDOS DE PRUEBA EN DIFERENTES ESTADOS ───────────────
        $estadosDemo = [
            [
                'estado' => 'PENDIENTE',
                'dias_atras' => 0,
                'usuario' => $cliente1,
                'direccion' => $dir1,
                'prod_idx' => 0, // Libreta
                'cant' => 2,
            ],
            [
                'estado' => 'EN_PROCESO',
                'dias_atras' => 1,
                'usuario' => $cliente2,
                'direccion' => $dir2,
                'prod_idx' => 1, // Resaltadores
                'cant' => 3,
            ],
            [
                'estado' => 'ENVIADO',
                'dias_atras' => 2,
                'usuario' => $cliente1,
                'direccion' => $dir1,
                'prod_idx' => 2, // Peluche mochi
                'cant' => 1,
            ],
            [
                'estado' => 'ENTREGADO',
                'dias_atras' => 5,
                'usuario' => $cliente2,
                'direccion' => $dir2,
                'prod_idx' => 4, // Washi tape
                'cant' => 2,
            ],
        ];

        $pedidosCreados = [];
        foreach ($estadosDemo as $idx => $pedConfig) {
            $prod = $productosCreados[$pedConfig['prod_idx']];
            $subtotal = $prod->precio * $pedConfig['cant'];
            $envio = $subtotal >= 150000 ? 0 : 8000;
            $total = $subtotal + $envio;
            $fecha = now()->subDays($pedConfig['dias_atras']);
            $numPedido = 'PED-'.$fecha->format('Ymd').'-'.str_pad($idx + 101, 4, '0', STR_PAD_LEFT);

            $pedido = Pedido::updateOrCreate(
                ['numero_pedido' => $numPedido],
                [
                    'id_usuario' => $pedConfig['usuario']->id_usuario,
                    'id_direccion' => $pedConfig['direccion']->id_direccion ?? null,
                    'subtotal' => $subtotal,
                    'descuento' => 0,
                    'costo_envio' => $envio,
                    'total' => $total,
                    'estado' => $pedConfig['estado'],
                    'observaciones' => 'Por favor empacar con cinta kawaii para regalo.',
                    'fecha_pedido' => $fecha,
                    'direccion_entrega' => "{$pedConfig['direccion']->calle}, {$pedConfig['direccion']->ciudad}. Tel: {$pedConfig['usuario']->telefono}",
                    'notas' => 'Por favor empacar con cinta kawaii para regalo.',
                    'creado_en' => $fecha,
                    'actualizado_en' => $fecha,
                ]
            );

            $pedidosCreados[] = $pedido;

            PedidoDetalle::updateOrCreate(
                [
                    'id_pedido' => $pedido->id_pedido,
                    'id_producto' => $prod->id_producto,
                ],
                [
                    'nombre_producto' => $prod->nombre,
                    'cantidad' => $pedConfig['cant'],
                    'precio_unitario' => $prod->precio,
                    'subtotal' => $subtotal,
                ]
            );

            Pago::updateOrCreate(
                ['id_pedido' => $pedido->id_pedido],
                [
                    'id_metodo_pago' => $metodoPse->id_metodo_pago,
                    'monto' => $total,
                    'estado' => $pedConfig['estado'] === 'ENTREGADO' ? 'PAGADO' : 'PENDIENTE',
                    'referencia' => 'REF-'.strtoupper(substr(md5($numPedido), 0, 10)),
                    'fecha_transaccion' => $fecha,
                    'fecha_pago' => $fecha,
                    'creado_en' => $fecha,
                ]
            );
        }

        // ── 10. RESEÑAS DE PRUEBA ────────────────────────────────────
        Resena::updateOrCreate(
            [
                'id_usuario' => $cliente1->id_usuario,
                'id_producto' => $productosCreados[0]->id_producto,
            ],
            [
                'id_pedido' => $pedidosCreados[0]->id_pedido ?? 1,
                'calificacion' => 5,
                'comentario' => '¡Me encantó! La pasta es súper resistente y las hojas no se traspasan con marcadores gel.',
                'activo' => 1,
                'creado_en' => now()->subDays(2),
            ]
        );

        Resena::updateOrCreate(
            [
                'id_usuario' => $cliente2->id_usuario,
                'id_producto' => $productosCreados[1]->id_producto,
            ],
            [
                'id_pedido' => $pedidosCreados[1]->id_pedido ?? 1,
                'calificacion' => 5,
                'comentario' => 'Los colores pastel son preciosos, súper suaves y no cansan la vista al estudiar.',
                'activo' => 1,
                'creado_en' => now()->subDay(),
            ]
        );
    }
}
