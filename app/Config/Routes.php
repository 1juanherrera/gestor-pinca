<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('api', function ($routes) {

    // HEALTH CHECK (público — excluido del filtro JWT)
    $routes->get('health', 'HealthController::index');

    // EMPRESA
    $routes->get('empresa',                'EmpresaController::empresa');
    $routes->get('empresa/logo-base64',    'EmpresaController::logoBase64');
    $routes->post('empresa/logo',          'EmpresaController::uploadLogo');
    $routes->delete('empresa/logo',        'EmpresaController::deleteLogo');

    // USUARIOS
    $routes->post('login',                  'UsuarioController::login');
    $routes->post('crear',                  'UsuarioController::crear');
    $routes->get('auth/me',                  'UsuarioController::me');
    $routes->post('auth/logout',             'UsuarioController::logout');
    $routes->post('auth/refresh',            'UsuarioController::refresh');
    $routes->patch('usuarios/mi-password',   'UsuarioController::cambiarPassword');
    $routes->patch('usuarios/mi-perfil',     'UsuarioController::actualizarPerfil');
    $routes->get('usuarios/mi-actividad',    'UsuarioController::miActividad');
    $routes->put('empresa',                  'EmpresaController::update');

    // ROLES Y PERMISOS
    $routes->get('roles/permisos',                       'PermisosController::index');
    $routes->get('roles/permisos/(:alpha)',               'PermisosController::show/$1');
    $routes->put('roles/(:alpha)/permisos',               'PermisosController::update/$1');
    $routes->get('roles/usuarios',                        'PermisosController::listarUsuarios');
    $routes->patch('roles/usuarios/(:num)/rol',           'PermisosController::cambiarRol/$1');

    // CATÁLOGO (Maestro de Ítems)
    $routes->get('catalogo',                         'CatalogoController::index');
    $routes->get('catalogo/(:num)/proveedores',      'CatalogoController::proveedores/$1');
    $routes->get('catalogo/(:num)',                   'CatalogoController::show/$1');
    $routes->post('catalogo',                         'CatalogoController::create');
    $routes->put('catalogo/(:num)',                   'CatalogoController::update/$1');
    $routes->delete('catalogo/(:num)',                'CatalogoController::delete/$1');
    $routes->post('catalogo/(:num)/restore',          'CatalogoController::restore/$1');

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
    // POST bodegas/item DESHABILITADO — stock solo ingresa por OC o Producción
    // PUT bodegas/item DESHABILITADO — bypaseaba capas y audit log (eliminado).
    $routes->get('bodegas/inventario/(:num)', 'BodegasController::bodega_inventario/$1');
    // PATCH inventario/cantidad DESHABILITADO — stock solo ingresa por OC o Producción
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
    // Versionado de fórmula
    $routes->get('formulaciones/(:num)/versiones',         'FormulacionesController::versiones/$1');
    $routes->get('formulaciones/versiones/(:num)',         'FormulacionesController::versionDetalle/$1');
    $routes->post('formulaciones/versiones/(:num)/restaurar', 'FormulacionesController::restaurarVersion/$1');
    $routes->get('formulaciones/recalcular_costos/(:num)/(:segment)', 'FormulacionesController::recalcular_costos_por_volumen/$1/$2');
    $routes->get('formulaciones/(:num)', 'FormulacionesController::show/$1');
    $routes->post('formulaciones',        'FormulacionesController::create');
    $routes->post('formulaciones/clonar', 'FormulacionesController::clonar');
    $routes->put('formulaciones/(:num)',  'FormulacionesController::update/$1');

    // COSTOS DE PRODUCCIÓN — vista agregada de costos finales por producto
    $routes->get('costos-produccion',           'CostosProduccionController::index');
    $routes->get('costos-produccion/(:num)',    'CostosProduccionController::show/$1');
    $routes->get('costos-produccion/(:num)/historia', 'CostosProduccionController::historia/$1');

    // SALUD DEL SISTEMA — dashboard de calidad de datos
    $routes->get('salud-sistema',               'SaludSistemaController::index');

    // PROVEEDORES
    $routes->get('proveedores', 'ProveedorController::proveedores');
    $routes->get('proveedor_items', 'ProveedorController::get_item_proveedores');
    $routes->get('proveedor_items/(:num)', 'ProveedorController::get_item_proveedores/$1');
    $routes->post('proveedores', 'ProveedorController::create');
    $routes->put('proveedores/(:num)', 'ProveedorController::update/$1');
    $routes->delete('proveedores/(:num)', 'ProveedorController::delete/$1');
    $routes->post('proveedores/(:num)/restore', 'ProveedorController::restore/$1');

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
    $routes->post('clientes/(:num)/restore', 'ClientesController::restore/$1');

    // FACTURAS
    $routes->get('facturas', 'FacturasController::index');
    $routes->get('facturas/(:num)', 'FacturasController::show/$1');
    $routes->get('facturas/(:num)/detalle', 'FacturasController::detalle/$1');
    $routes->get('facturas/(:num)/abonos', 'FacturasController::abonos/$1');
    $routes->get('facturas/(:num)/remision', 'FacturasController::remision/$1');
    $routes->post('facturas', 'FacturasController::create');
    $routes->post('facturas/bulk/cambiar-estado', 'FacturasController::bulkCambiarEstado');
    $routes->put('facturas/(:num)', 'FacturasController::update/$1');
    $routes->patch('facturas/(:num)/estado', 'FacturasController::cambiarEstado/$1');
    $routes->delete('facturas/(:num)', 'FacturasController::delete/$1');

    // INVENTARIO
    $routes->get('inventario/global',          'InventarioController::global');
    $routes->post('inventario/traspaso',       'InventarioController::traspaso');
    $routes->post('inventario/ajuste-manual',  'InventarioController::ajusteManual');
    // POST inventario/ingresar DESHABILITADO — stock solo ingresa por OC o Producción
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
    $routes->get('categorias',                 'CategoriaController::categorias');
    $routes->get('categorias/(:num)',          'CategoriaController::show/$1');
    $routes->post('categorias',                'CategoriaController::create');
    $routes->put('categorias/(:num)',          'CategoriaController::update/$1');
    $routes->delete('categorias/(:num)',       'CategoriaController::delete/$1');

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
    $routes->post('requisiciones/sugerir-mrp',           'RequisicionesCompraController::sugerirMRP');
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

    // PANEL PRINCIPAL — KPIs consolidados
    $routes->get('dashboard', 'DashboardController::index');

    // NOTIFICACIONES
    $routes->get('notificaciones',                  'NotificacionesController::index');
    $routes->get('notificaciones/no-leidas',        'NotificacionesController::noLeidas');
    $routes->patch('notificaciones/(:num)/leer',    'NotificacionesController::marcarLeida/$1');
    $routes->post('notificaciones/leer-todas',      'NotificacionesController::marcarTodasLeidas');

    // TRAZABILIDAD DE LOTE
    $routes->get('trazabilidad/lotes',                'TrazabilidadController::lotes');
    $routes->get('trazabilidad/preparacion/(:num)',   'TrazabilidadController::porPreparacion/$1');
    $routes->get('trazabilidad/lote/(:any)',          'TrazabilidadController::porLote/$1');

    // SINCRONIZACIÓN (auditoría catálogo ↔ proveedores)
    $routes->get('sincronizacion/stats',       'SincronizacionController::stats');
    $routes->get('sincronizacion/maestro',     'SincronizacionController::maestro');
    $routes->get('sincronizacion/pendientes',  'SincronizacionController::pendientes');
    $routes->get('sincronizacion/duplicados',  'SincronizacionController::duplicados');
    $routes->get('sincronizacion/huerfanos',   'SincronizacionController::huerfanos');
    $routes->post('sincronizacion/merge',      'SincronizacionController::merge');
    // Reemplazo manual de materia prima en fórmulas (buscar y reemplazar A→B en el BOM)
    $routes->get('sincronizacion/uso-formulas/(:num)', 'SincronizacionController::usoEnFormulas/$1');
    $routes->post('sincronizacion/reemplazar-formula', 'SincronizacionController::reemplazarFormula');
    $routes->get('sincronizacion/reemplazos', 'SincronizacionController::historialReemplazos');
    $routes->post('sincronizacion/reemplazos/(:num)/revertir', 'SincronizacionController::revertirReemplazo/$1');

    // Deduplicación asistida por IA (clusters de identidad química)
    $routes->post('sincronizacion/ia/clasificar',                  'SincronizacionController::iaClasificar');
    $routes->get('sincronizacion/ia/clusters',                     'SincronizacionController::iaClusters');
    $routes->get('sincronizacion/ia/clusters/(:num)',              'SincronizacionController::iaCluster/$1');
    $routes->patch('sincronizacion/ia/clusters/(:num)',            'SincronizacionController::iaActualizarCluster/$1');
    $routes->post('sincronizacion/ia/clusters/(:num)/fusionar',    'SincronizacionController::iaFusionarGrupo/$1');
    $routes->post('sincronizacion/ia/clusters/(:num)/descartar',   'SincronizacionController::iaDescartarCluster/$1');
    $routes->patch('sincronizacion/ia/cluster-items/(:num)',       'SincronizacionController::iaMoverItem/$1');
    $routes->get('sincronizacion/ia/verificar/(:num)',             'SincronizacionController::iaVerificar/$1');
    $routes->post('sincronizacion/ia/auditoria/(:num)/revertir',   'SincronizacionController::iaRevertir/$1');

    // BÚSQUEDA GLOBAL (Cmd+K palette)
    $routes->get('search', 'SearchController::search');

    // CONFIGURACIÓN DEL SISTEMA (parámetros globales: tributaria, umbrales, numeración, …)
    // Específicas PRIMERO (antes del :clave genérico)
    $routes->get('configuracion',                       'ConfiguracionController::index');
    $routes->put('configuracion/bulk',                  'ConfiguracionController::bulkUpdate');
    $routes->get('configuracion/tipos-movimiento',      'ConfiguracionController::tiposMovimiento');
    $routes->get('configuracion/grupo/(:segment)',      'ConfiguracionController::porGrupo/$1');
    $routes->get('configuracion/(:segment)',            'ConfiguracionController::show/$1');
    $routes->put('configuracion/(:segment)',            'ConfiguracionController::update/$1');

    // NUMERACIÓN DE DOCUMENTOS
    $routes->get('numeracion',          'NumeracionController::index');
    $routes->post('numeracion',         'NumeracionController::create');
    $routes->put('numeracion/(:num)',   'NumeracionController::update/$1');

    // AUDITORÍA (admin only)
    $routes->get('auditoria/login-attempts', 'AuditoriaController::loginAttempts');
    $routes->get('auditoria/movimientos',    'AuditoriaController::movimientos');

    // ÓRDENES DE COMPRA
    $routes->get('ordenes_compra',                        'OrdenesCompraController::index');
    // ✅ Específicas PRIMERO
    $routes->get('ordenes_compra/(:num)/detalle',         'OrdenesCompraController::detalle/$1');
    $routes->patch('ordenes_compra/(:num)/estado',        'OrdenesCompraController::cambiarEstado/$1');
    $routes->get('ordenes_compra/(:num)/lote-sugerido',            'OrdenesCompraController::loteSugerido/$1');
    $routes->post('ordenes_compra/(:num)/recibir/(:num)',          'OrdenesCompraController::recibirLinea/$1/$2');
    $routes->post('ordenes_compra/(:num)/recibir-prorrateado',     'OrdenesCompraController::recibirLoteProrrateado/$1');
    // ✅ Genéricas DESPUÉS
    $routes->get('ordenes_compra/(:num)',                 'OrdenesCompraController::show/$1');
    $routes->post('ordenes_compra',                       'OrdenesCompraController::create');
    $routes->put('ordenes_compra/(:num)',                 'OrdenesCompraController::update/$1');
    $routes->delete('ordenes_compra/(:num)',              'OrdenesCompraController::delete/$1');
});