<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MovimientoInventarioModel;

class MovimientoInventarioController extends ResourceController
{
    protected $modelName = MovimientoInventarioModel::class;

    public function index()
    {
        $page = (int) $this->request->getGet('page') ?: 1;
        $limit = (int) $this->request->getGet('limit') ?: 50;

        $filtros = [
            'item_general_id' => $this->request->getGet('item_general_id'),
            'bodega_id'       => $this->request->getGet('bodega_id'),
            'tipo_movimiento' => $this->request->getGet('tipo_movimiento'),
            'referencia_tipo' => $this->request->getGet('referencia_tipo'),
            'fecha_inicio'    => $this->request->getGet('fecha_inicio'),
            'fecha_fin'       => $this->request->getGet('fecha_fin'),
            'search'          => $this->request->getGet('search'),
        ];

        $data = $this->model->get_movimientos($filtros, $page, $limit);
        return $this->respond($data);
    }
}
