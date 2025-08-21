<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('api', function ($routes) {
    $routes->get('item_general', 'ItemGeneralController::item_general');
    $routes->get('formulaciones', 'ItemGeneralController::item_formulaciones');
    $routes->get('items', 'ItemGeneralController::get_items_all');
    $routes->get('item_general/(:num)', 'ItemGeneralController::show/$1');
    $routes->post('item_general', 'ItemGeneralController::create');
    $routes->put('item_general/(:num)', 'ItemGeneralController::update/$1');
    $routes->delete('item_general/(:num)', 'ItemGeneralController::delete/$1');

    // INSTALACIONES
    $routes->get('instalaciones', 'InstalacionesController::instalaciones');

    // BODEGAS
    $routes->get('bodegas', 'BodegasController::bodegas');
    $routes->get('bodegas/(:num)', 'BodegasController::bodegas/$1');
});

