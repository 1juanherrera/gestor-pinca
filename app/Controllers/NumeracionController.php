<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\NumeracionModel;
use App\Traits\JwtUserAware;

/**
 * NumeracionController — gestión de series correlativas (vista admin).
 *
 * El consumo (reservar) se hace internamente desde Facturas/OC/etc — no
 * existe endpoint público de reserva.
 */
class NumeracionController extends ResourceController
{
    use JwtUserAware;

    protected $modelName = NumeracionModel::class;
    protected $format    = 'json';

    /** GET /api/numeracion → lista todas las series con folios restantes */
    public function index()
    {
        $rows = $this->model->orderBy('tipo_doc')->orderBy('activo', 'DESC')->findAll();

        foreach ($rows as &$r) {
            $r['folios_restantes'] = (!empty($r['rango_max']))
                ? max(0, (int) $r['rango_max'] - (int) $r['proximo_numero'] + 1)
                : null;
            $r['ejemplo_proximo'] = $this->model->formatear($r['prefijo'], (int) $r['padding'], (int) $r['proximo_numero']);
        }

        return $this->respond($rows);
    }

    /** PUT /api/numeracion/:id — admin edita una serie */
    public function update($id = null)
    {
        if (!$this->userHasRole('admin')) {
            return $this->failForbidden('Solo administradores pueden modificar la numeración.');
        }

        $existente = $this->model->find($id);
        if (!$existente) return $this->failNotFound("Serie #$id no encontrada.");

        $body = $this->request->getJSON(true) ?? [];

        $payload = array_intersect_key($body, array_flip([
            'prefijo', 'padding', 'proximo_numero', 'reinicia_anual',
            'resolucion_dian', 'fecha_resolucion', 'rango_min', 'rango_max',
            'fecha_vigencia_hasta', 'activo',
        ]));
        $payload['updated_at'] = date('Y-m-d H:i:s');
        $payload['updated_by'] = $this->getUsername();

        // Si activo cambia a 1, desactivar otras series del mismo tipo_doc
        if (!empty($payload['activo']) && (int) $payload['activo'] === 1) {
            $this->db->table('numeracion_documentos')
                ->where('tipo_doc', $existente['tipo_doc'])
                ->where('id_numeracion !=', $id)
                ->update(['activo' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $ok = $this->model->update($id, $payload);
        if (!$ok) return $this->fail('No se pudo actualizar la serie.');

        return $this->respond([
            'mensaje' => "Serie #$id actualizada",
            'serie'   => $this->model->find($id),
        ]);
    }

    /** POST /api/numeracion — admin crea nueva serie (típicamente nueva resolución DIAN) */
    public function create()
    {
        if (!$this->userHasRole('admin')) {
            return $this->failForbidden('Solo administradores pueden crear series.');
        }

        $body = $this->request->getJSON(true) ?? [];
        if (empty($body['tipo_doc']) || empty($body['prefijo'])) {
            return $this->failValidationErrors('`tipo_doc` y `prefijo` son obligatorios.');
        }

        // Si la nueva queda activa, desactivar las anteriores del mismo tipo
        $activarNueva = !isset($body['activo']) || (int) $body['activo'] === 1;
        if ($activarNueva) {
            $this->db->table('numeracion_documentos')
                ->where('tipo_doc', $body['tipo_doc'])
                ->update(['activo' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $body['activo']     = $activarNueva ? 1 : 0;
        $body['created_at'] = date('Y-m-d H:i:s');
        $body['updated_at'] = date('Y-m-d H:i:s');
        $body['updated_by'] = $this->getUsername();

        $id = $this->model->insert($body, true);
        if (!$id) return $this->fail('No se pudo crear la serie.');

        return $this->respondCreated([
            'mensaje' => "Serie '{$body['tipo_doc']}' creada",
            'serie'   => $this->model->find($id),
        ]);
    }
}
