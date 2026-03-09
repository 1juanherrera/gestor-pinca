<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('api', function ($routes) {
// USUARIOS
    $routes->post('login', 'UsuarioController::login');
    $routes->post('crear', 'UsuarioController::crear');
});
// ['filter' => 'jwt'],
$routes->group('api', function ($routes) {
    // EMPRESA
    $routes->get('empresa', 'EmpresaController::empresa');

    // USUARIOS
    $routes->post('login', 'UsuarioController::login');
    $routes->post('crear', 'UsuarioController::crear');

    // ITEMS
    $routes->get('item_general', 'ItemController::item_general');
    $routes->get('items', 'ItemController::get_items_all');
    $routes->get('item_general/(:num)', 'ItemController::show/$1');
    $routes->post('item_general', 'ItemController::create');
    $routes->put('item_general/(:num)', 'ItemController::update/$1');
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
    $routes->post('bodegas/item', 'BodegasController::createItemBodega');  
    $routes->put('bodegas/item/(:num)', 'BodegasController::updateItemBodega/$1');
    $routes->get('bodegas/inventario/(:num)', 'BodegasController::bodega_inventario/$1');
    $routes->get('bodegas/(:num)', 'BodegasController::show/$1');
    $routes->post('bodegas', 'BodegasController::create');
    $routes->put('bodegas/(:num)', 'BodegasController::update/$1');
    $routes->delete('bodegas/(:num)', 'BodegasController::delete/$1');

    
    // FORMULACIONES
    $routes->get('formulaciones', 'FormulacionesController::formulaciones');
    $routes->get('formulaciones/(:num)', 'FormulacionesController::show/$1');
    $routes->get('formulacion_item/(:num)',  'FormulacionesController::showItem/$1');
    $routes->get('formulaciones/costos/(:num)', 'FormulacionesController::calcular_costos_volumen/$1');
    $routes->get('formulaciones/recalcular_costos/(:num)/(:segment)', 'FormulacionesController::recalcular_costos_por_volumen/$1/$2');

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

    // COSTOS ITEM
    $routes->put('costos_item/(:num)', 'CostosItemController::update/$1');

    // UNIDADES
    $routes->get('unidades', 'UnidadController::unidades');
    $routes->get('unidades/(:num)', 'UnidadController::show/$1');
    $routes->post('unidades', 'UnidadController::create');
    $routes->put('unidades/(:num)', 'UnidadController::update/$1');
    $routes->delete('unidades/(:num)', 'UnidadController::delete/$1');

    // CATEGORIAS
    $routes->get('categorias', 'CategoriaController::categorias');

    // PREPARACIONES    
    $routes->get('preparaciones',              'PreparacionesController::index');
    $routes->post('preparaciones',             'PreparacionesController::create');
    $routes->get('preparaciones/item/(:num)',  'PreparacionesController::byItem/$1');
    $routes->get('preparaciones/(:num)',       'PreparacionesController::show/$1');
    $routes->put('preparaciones/(:num)',       'PreparacionesController::update/$1'); 

    $routes->get   ('pagos_cliente',                 'PagosClienteController::index');
    $routes->get   ('pagos_cliente/(:num)',           'PagosClienteController::show/$1');
    $routes->post  ('pagos_cliente',                 'PagosClienteController::create');
    $routes->put   ('pagos_cliente/(:num)',           'PagosClienteController::update/$1');
    $routes->delete('pagos_cliente/(:num)',           'PagosClienteController::delete/$1');

    // ── COTIZACIONES  (?cliente_id=X) ─────────────────────────────────────
    $routes->get   ('cotizaciones',                  'CotizacionesController::index');
    $routes->get   ('cotizaciones/(:num)',            'CotizacionesController::show/$1');
    $routes->get   ('cotizaciones/(:num)/detalle',   'CotizacionesController::detalle/$1');
    $routes->post  ('cotizaciones',                  'CotizacionesController::create');
    $routes->put   ('cotizaciones/(:num)',            'CotizacionesController::update/$1');
    $routes->patch ('cotizaciones/(:num)/estado',    'CotizacionesController::cambiarEstado/$1');
    $routes->post  ('cotizaciones/(:num)/convertir', 'CotizacionesController::convertir/$1');
    $routes->delete('cotizaciones/(:num)',            'CotizacionesController::delete/$1');

    // ── REMISIONES  (?cliente_id=X | ?factura_id=X) ───────────────────────
    $routes->get   ('remisiones',                    'RemisionesController::index');
    $routes->get   ('remisiones/(:num)',              'RemisionesController::show/$1');
    $routes->get   ('remisiones/(:num)/detalle',     'RemisionesController::detalle/$1');
    $routes->post  ('remisiones',                    'RemisionesController::create');
    $routes->put   ('remisiones/(:num)',              'RemisionesController::update/$1');
    $routes->patch ('remisiones/(:num)/estado',      'RemisionesController::cambiarEstado/$1');
    $routes->post  ('remisiones/(:num)/convertir',   'RemisionesController::convertir/$1');
    $routes->delete('remisiones/(:num)',              'RemisionesController::delete/$1');
});