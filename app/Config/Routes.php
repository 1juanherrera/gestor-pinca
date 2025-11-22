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
    $routes->get('bodegas/(:num)', 'BodegasController::show/$1');
    $routes->post('bodegas', 'BodegasController::create');
    $routes->put('bodegas/(:num)', 'BodegasController::update/$1');
    $routes->delete('bodegas/(:num)', 'BodegasController::delete/$1');
    $routes->get('bodegas/inventario/(:num)', 'BodegasController::bodega_inventario/$1');

    // FORMULACIONES
    $routes->get('formulaciones', 'FormulacionesController::formulaciones');
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
    $routes->get('facturas', 'FacturasController::facturas');
    $routes->get('facturas/(:num)', 'FacturasController::show/$1');
    $routes->post('facturas', 'FacturasController::create');
    $routes->put('facturas/(:num)', 'FacturasController::update/$1');
    $routes->delete('facturas/(:num)', 'FacturasController::delete/$1');
});

