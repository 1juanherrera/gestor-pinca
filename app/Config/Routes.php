<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('api', function ($routes) {
    // EMPRESA
    $routes->get('empresa', 'EmpresaController::empresa');

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

    // FORMULACIONES
    $routes->get('formulaciones', 'FormulacionesController::formulaciones');
    $routes->get('formulaciones/costos/(:num)', 'FormulacionesController::calcular_costos_volumen/$1');
    $routes->get('formulaciones/recalcular_costos/(:num)/(:segment)', 'FormulacionesController::recalcular_costos_por_volumen/$1/$2');
});

