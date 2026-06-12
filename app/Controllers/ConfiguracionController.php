<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ConfiguracionModel;
use App\Traits\JwtUserAware;

/**
 * ConfiguracionController — CRUD de parámetros del sistema.
 *
 * Lectura: cualquier usuario autenticado.
 * Mutaciones: solo `rol=admin` (validado vía JwtUserAware).
 */
class ConfiguracionController extends ResourceController
{
    use \App\Traits\ApiResponse;

    use JwtUserAware;

    protected $modelName = ConfiguracionModel::class;
    protected $format    = 'json';

    /** GET /api/configuracion → todo agrupado */
    public function index()
    {
        return $this->respond($this->model->getAllGrouped());
    }

    /** GET /api/configuracion/grupo/:grupo */
    public function porGrupo(string $grupo)
    {
        return $this->respond($this->model->getGrupo($grupo));
    }

    /** GET /api/configuracion/:clave */
    public function show($clave = null)
    {
        if (!$clave) return $this->apiFail('Clave requerida.', 422);
        $valor = $this->model->obtener($clave, null);
        if ($valor === null) return $this->apiNotFound("Clave '$clave' no encontrada.");
        return $this->respond(['clave' => $clave, 'valor' => $valor]);
    }

    /** PUT /api/configuracion/:clave  body: { valor: ... } */
    public function update($clave = null)
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo administradores pueden modificar la configuración.');
        }

        if (!$clave) return $this->apiFail('Clave requerida.', 422);

        $body = $this->request->getJSON(true);
        if (!array_key_exists('valor', $body ?? [])) {
            return $this->apiFail('Campo `valor` requerido.', 422);
        }

        $ok = $this->model->guardar($clave, $body['valor'], $this->getUsername());
        if (!$ok) return $this->apiFail("No se pudo actualizar '$clave'.");

        return $this->respond([
            'mensaje' => "Configuración '$clave' actualizada",
            'clave'   => $clave,
            'valor'   => $body['valor'],
        ]);
    }

    /**
     * GET /api/configuracion/tipos-movimiento
     * Devuelve el catálogo enum de tipos y referencias usados por
     * MovimientoInventarioModel (read-only — son constantes del dominio).
     */
    public function tiposMovimiento()
    {
        return $this->respond([
            'tipos' => [
                ['key' => 'ENTRADA',  'label' => 'Entrada',  'tone' => 'success'],
                ['key' => 'SALIDA',   'label' => 'Salida',   'tone' => 'danger'],
                ['key' => 'TRASPASO', 'label' => 'Traspaso', 'tone' => 'info'],
                ['key' => 'AJUSTE',   'label' => 'Ajuste',   'tone' => 'warning'],
            ],
            'referencias' => [
                ['key' => 'OC',                'label' => 'Orden de compra'],
                ['key' => 'FACTURA_VENTA',     'label' => 'Factura de venta'],
                ['key' => 'REMISION',          'label' => 'Remisión'],
                ['key' => 'PRODUCCION',        'label' => 'Producción'],
                ['key' => 'TRASPASO_BODEGA',   'label' => 'Traspaso entre bodegas'],
                ['key' => 'AJUSTE_MANUAL',     'label' => 'Ajuste manual'],
                ['key' => 'ANULACION',         'label' => 'Anulación'],
            ],
        ]);
    }

    /** PUT /api/configuracion/bulk  body: { configs: { clave: valor, … } } */
    public function bulkUpdate()
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo administradores pueden modificar la configuración.');
        }

        $body = $this->request->getJSON(true);
        $configs = $body['configs'] ?? null;
        if (!is_array($configs) || empty($configs)) {
            return $this->apiFail('Body debe contener `configs: { clave: valor, … }`.', 422);
        }

        $usuario   = $this->getUsername();
        $aplicados = [];
        $errores   = [];

        foreach ($configs as $clave => $valor) {
            $ok = $this->model->guardar($clave, $valor, $usuario);
            if ($ok) $aplicados[] = $clave;
            else     $errores[]   = $clave;
        }

        return $this->respond([
            'mensaje'   => count($aplicados) . ' configuraciones actualizadas',
            'aplicados' => $aplicados,
            'errores'   => $errores,
        ]);
    }
}
