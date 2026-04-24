<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('api', function ($routes) {

    // EMPRESA
    $routes->get('empresa', 'EmpresaController::empresa');

    // USUARIOS
    $routes->post('login', 'UsuarioController::login');
    $routes->post('crear', 'UsuarioController::crear');

    // ITEMS
    $routes->get('item_general', 'ItemController::item_general');
    $routes->get('items/materias_disponibles', 'ItemController::materias_disponibles');
    $routes->get('items', 'ItemController::get_items_all');
    $routes->get('item_general/buscar',            'ItemController::buscar');
    $routes->get('item_general/(:num)/inventario', 'ItemController::inventario_por_item/$1');
    $routes->get('item_general/(:num)',            'ItemController::show/$1');
    $routes->post('item_general', 'ItemController::create');
    $routes->put('item_general/(:num)', 'ItemController::update/$1');
    $routes->patch('item_general/(:num)/precio-manual', 'ItemController::updatePrecioManual/$1');
    $routes->delete('item_general/(:num)', 'ItemController::delete/$1');

    // INSTALACIONES
    $routes->get('instalaciones', 'InstalacionesController::instalaciones');
    $routes->get('instalaciones/bodegas/(:num)', 'InstalacionesController::instalaciones_with_bodegas/$1');
    $routes->get('instalaciones/(:num)', 'InstalacionesController::show/$1');
    $routes->post('instalaciones', 'InstalacionesController::create');
    $routes->put('instalaciones/(:num)', 'InstalacionesController::update/$1');
    $routes->delete('instalaciones/(:num)', 'InstalacionesController::delete/$1');

    // BODEGAS
    $routes->get('bodegas', 'BodegasController::bodegas');
    $routes->post('bodegas/item', 'BodegasController::create_item_bodega');
    $routes->put('bodegas/item/(:num)', 'BodegasController::update_item_bodega/$1');
    $routes->get('bodegas/inventario/(:num)', 'BodegasController::bodega_inventario/$1');
    $routes->patch('inventario/(:num)/cantidad', 'BodegasController::patch_cantidad/$1');
    $routes->get('bodegas/(:num)', 'BodegasController::show/$1');
    $routes->post('bodegas', 'BodegasController::create');
    $routes->put('bodegas/(:num)', 'BodegasController::update/$1');
    $routes->delete('bodegas/(:num)', 'BodegasController::delete/$1');

    // FORMULACIONES
    $routes->get('formulaciones', 'FormulacionesController::formulaciones');
    $routes->get('formulacion_item/(:num)', 'FormulacionesController::showItem/$1');
    $routes->get('formulaciones/costos/(:num)/proveedor/(:num)', 'FormulacionesController::calcular_costos_por_proveedor/$1/$2');
    $routes->get('formulaciones/costos/(:num)', 'FormulacionesController::calcular_costos_volumen/$1');
    $routes->get('formulaciones/(:num)/proveedores', 'FormulacionesController::proveedores_formulacion/$1');
    $routes->get('formulaciones/(:num)/opciones-ingredientes', 'FormulacionesController::opciones_proveedor_ingrediente/$1');
    $routes->get('formulaciones/recalcular_costos/(:num)/(:segment)', 'FormulacionesController::recalcular_costos_por_volumen/$1/$2');
    $routes->get('formulaciones/(:num)', 'FormulacionesController::show/$1');
    $routes->post('formulaciones',        'FormulacionesController::create');
    $routes->put('formulaciones/(:num)',  'FormulacionesController::update/$1');

    // PROVEEDORES
    $routes->get('proveedores', 'ProveedorController::proveedores');
    $routes->get('proveedor_items', 'ProveedorController::get_item_proveedores');
    $routes->get('proveedor_items/(:num)', 'ProveedorController::get_item_proveedores/$1');
    $routes->post('proveedores', 'ProveedorController::create');
    $routes->put('proveedores/(:num)', 'ProveedorController::update/$1');
    $routes->delete('proveedores/(:num)', 'ProveedorController::delete/$1');

    // ITEM PROVEEDORES
    $routes->get('item_proveedores', 'ItemProveedorController::get_item_proveedores');
    $routes->get('item_proveedores/(:num)', 'ItemProveedorController::show/$1');
    $routes->post('item_proveedores', 'ItemProveedorController::create');
    $routes->put('item_proveedores/(:num)', 'ItemProveedorController::update/$1');
    $routes->delete('item_proveedores/(:num)', 'ItemProveedorController::delete/$1');
    $routes->patch('item_proveedores/(:num)/vincular', 'ItemProveedorController::vincular/$1');

    // CLIENTES
    $routes->get('clientes', 'ClientesController::clientes');
    $routes->get('clientes/(:num)', 'ClientesController::show/$1');
    $routes->post('clientes', 'ClientesController::create');
    $routes->put('clientes/(:num)', 'ClientesController::update/$1');
    $routes->delete('clientes/(:num)', 'ClientesController::delete/$1');

    // FACTURAS
    $routes->get('facturas', 'FacturasController::index');
    $routes->get('facturas/(:num)', 'FacturasController::show/$1');
    $routes->get('facturas/(:num)/detalle', 'FacturasController::detalle/$1');
    $routes->get('facturas/(:num)/abonos', 'FacturasController::abonos/$1');
    $routes->get('facturas/(:num)/remision', 'FacturasController::remision/$1');
    $routes->post('facturas', 'FacturasController::create');
    $routes->put('facturas/(:num)', 'FacturasController::update/$1');
    $routes->patch('facturas/(:num)/estado', 'FacturasController::cambiarEstado/$1');
    $routes->delete('facturas/(:num)', 'FacturasController::delete/$1');

    // INVENTARIO
    $routes->post('inventario/traspaso', 'InventarioController::traspaso');
    $routes->post('inventario/ingresar', 'InventarioController::ingresarABodega');
    $routes->delete('inventario/(:num)/bodega/(:num)', 'InventarioController::removeFromBodega/$1/$2');

    // CAPAS DE INVENTARIO
    $routes->get('inventario/capas/bodegas',              'CapasInventarioController::bodegasConCapas');
    $routes->get('inventario/(:num)/capas',               'CapasInventarioController::capas/$1');
    $routes->get('inventario/capas/preparacion/(:num)',    'CapasInventarioController::consumosPorPreparacion/$1');

    // COSTOS ITEM
    $routes->put('costos_item/(:num)', 'CostosItemController::update/$1');

    // COSTOS INDIRECTOS
    $routes->get('costos_indirectos/resumen',     'CostosIndirectosController::resumen');
    $routes->get('costos_indirectos/item/(:num)', 'CostosIndirectosController::costosItem/$1');
    $routes->post('costos_indirectos/item/(:num)','CostosIndirectosController::asignarItem/$1');
    $routes->get('costos_indirectos/(:num)',      'CostosIndirectosController::show/$1');
    $routes->get('costos_indirectos',             'CostosIndirectosController::index');
    $routes->post('costos_indirectos',            'CostosIndirectosController::create');
    $routes->put('costos_indirectos/(:num)',      'CostosIndirectosController::update/$1');
    $routes->delete('costos_indirectos/(:num)',   'CostosIndirectosController::delete/$1');

    // UNIDADES
    $routes->get('unidades', 'UnidadController::unidades');
    $routes->get('unidades/(:num)', 'UnidadController::show/$1');
    $routes->post('unidades', 'UnidadController::create');
    $routes->put('unidades/(:num)', 'UnidadController::update/$1');
    $routes->delete('unidades/(:num)', 'UnidadController::delete/$1');

    // CATEGORIAS
    $routes->get('categorias', 'CategoriaController::categorias');

    // PREPARACIONES
    $routes->get('preparaciones', 'PreparacionesController::index');
    $routes->post('preparaciones', 'PreparacionesController::create');
    $routes->get('preparaciones/costos_resumen',          'PreparacionesController::costosResumen');
    $routes->get('preparaciones/verificar-disponibilidad','RequisicionesCompraController::verificarDisponibilidad');
    $routes->get('preparaciones/item/(:num)',              'PreparacionesController::byItem/$1');
    $routes->get('preparaciones/(:num)',                   'PreparacionesController::show/$1');
    $routes->put('preparaciones/(:num)',                   'PreparacionesController::update/$1');
    // Costos indirectos por preparación
    $routes->post('preparaciones/(:num)/costos',             'PreparacionesController::addCosto/$1');
    $routes->put('preparaciones/(:num)/costos/(:num)',        'PreparacionesController::updateCosto/$1/$2');
    $routes->delete('preparaciones/(:num)/costos/(:num)',     'PreparacionesController::deleteCosto/$1/$2');

    // REQUISICIONES DE COMPRA
    $routes->get('requisiciones',                        'RequisicionesCompraController::index');
    $routes->post('requisiciones',                       'RequisicionesCompraController::create');
    $routes->post('requisiciones/convertir-oc',          'RequisicionesCompraController::convertirAOC');
    $routes->get('requisiciones/preparacion/(:num)',     'RequisicionesCompraController::porPreparacion/$1');
    $routes->patch('requisiciones/(:num)/estado',        'RequisicionesCompraController::actualizarEstado/$1');

    // PAGOS CLIENTE
    $routes->get('pagos_cliente', 'PagosClienteController::index');
    $routes->get('pagos_cliente/(:num)', 'PagosClienteController::show/$1');
    $routes->post('pagos_cliente', 'PagosClienteController::create');
    $routes->put('pagos_cliente/(:num)', 'PagosClienteController::update/$1');
    $routes->delete('pagos_cliente/(:num)', 'PagosClienteController::delete/$1');

    // CARTERA
    $routes->get('cartera/resumen', 'CarteraController::resumen');
    $routes->get('cartera/aging', 'CarteraController::aging');
    $routes->get('cartera/estado_cuenta/(:num)', 'CarteraController::estadoCuenta/$1');

    // GESTIONES DE COBRO
    $routes->get('gestiones_cobro', 'GestionesCobroController::index');
    $routes->get('gestiones_cobro/(:num)', 'GestionesCobroController::show/$1');
    $routes->post('gestiones_cobro', 'GestionesCobroController::create');
    $routes->put('gestiones_cobro/(:num)', 'GestionesCobroController::update/$1');
    $routes->delete('gestiones_cobro/(:num)', 'GestionesCobroController::delete/$1');

    // NOTAS CRÉDITO
    $routes->get('notas_credito', 'NotasCreditoController::index');
    $routes->get('notas_credito/(:num)', 'NotasCreditoController::show/$1');
    $routes->post('notas_credito', 'NotasCreditoController::create');
    $routes->patch('notas_credito/(:num)/anular', 'NotasCreditoController::anular/$1');

    // COTIZACIONES
    $routes->get('cotizaciones', 'CotizacionesController::index');
    $routes->get('cotizaciones/(:num)', 'CotizacionesController::show/$1');
    $routes->get('cotizaciones/(:num)/detalle', 'CotizacionesController::detalle/$1');
    $routes->post('cotizaciones', 'CotizacionesController::create');
    $routes->put('cotizaciones/(:num)', 'CotizacionesController::update/$1');
    $routes->patch('cotizaciones/(:num)/estado', 'CotizacionesController::cambiarEstado/$1');
    $routes->post('cotizaciones/(:num)/convertir', 'CotizacionesController::convertir/$1');
    $routes->delete('cotizaciones/(:num)', 'CotizacionesController::delete/$1');

    // REMISIONES
    $routes->get('remisiones', 'RemisionesController::index');
    $routes->get('remisiones/(:num)', 'RemisionesController::show/$1');
    $routes->get('remisiones/(:num)/detalle', 'RemisionesController::detalle/$1');
    $routes->post('remisiones', 'RemisionesController::create');
    $routes->put('remisiones/(:num)', 'RemisionesController::update/$1');
    $routes->patch('remisiones/(:num)/estado', 'RemisionesController::cambiarEstado/$1');
    $routes->post('remisiones/(:num)/convertir', 'RemisionesController::convertir/$1');
    $routes->delete('remisiones/(:num)', 'RemisionesController::delete/$1');

    // COMPARADOR
    $routes->get('comparador/por_item', 'ComparadorController::por_item');
    $routes->get('comparador/por_proveedor/(:num)', 'ComparadorController::por_proveedor/$1');
    $routes->get('comparador/historial/(:num)', 'ComparadorController::historial/$1');
    
    // MOVIMIENTOS DE INVENTARIO
    $routes->get('movimientos', 'MovimientoInventarioController::index');

    // TAMBORES
    $routes->get('tambores/disponibles',        'TamborController::disponibles');
    $routes->get('tambores/(:num)',              'TamborController::show/$1');
    $routes->get('tambores',                     'TamborController::index');
    $routes->post('tambores',                    'TamborController::create');
    $routes->put('tambores/(:num)',              'TamborController::update/$1');
    $routes->post('tambores/(:num)/consumir',   'TamborController::consumir/$1');

    // ÓRDENES DE COMPRA
    $routes->get('ordenes_compra',                        'OrdenesCompraController::index');
    // ✅ Específicas PRIMERO
    $routes->get('ordenes_compra/(:num)/detalle',         'OrdenesCompraController::detalle/$1');
    $routes->patch('ordenes_compra/(:num)/estado',        'OrdenesCompraController::cambiarEstado/$1');
    $routes->post('ordenes_compra/(:num)/recibir/(:num)', 'OrdenesCompraController::recibirLinea/$1/$2');
    // ✅ Genéricas DESPUÉS
    $routes->get('ordenes_compra/(:num)',                 'OrdenesCompraController::show/$1');
    $routes->post('ordenes_compra',                       'OrdenesCompraController::create');
    $routes->put('ordenes_compra/(:num)',                 'OrdenesCompraController::update/$1');
    $routes->delete('ordenes_compra/(:num)',              'OrdenesCompraController::delete/$1');
});