<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EmpresaModel;

class EmpresaController extends ResourceController
{
    protected $modelName = EmpresaModel::class;

    public function empresa()
    {
        $empresa = $this->model->get_all('empresa');
        if (!is_array($empresa)) $empresa = [$empresa];
        return $this->respond($empresa);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        if (!$data) return $this->fail('Datos inválidos', 400);

        $empresa = $this->model->get_all('empresa');
        if (empty($empresa)) return $this->failNotFound('No se encontró el registro de empresa.');

        $idEmpresa = $empresa[0]['id_empresa'];
        $allowed   = ['nit', 'razon_social', 'descripcion', 'ciudad', 'telefono', 'pagina_web'];
        $update    = array_intersect_key($data, array_flip($allowed));

        if (empty($update)) return $this->fail('No se enviaron campos válidos.', 400);

        $this->model->update_table($idEmpresa, $update, 'empresa');

        $updated = $this->model->get_all('empresa');
        return $this->respond(['ok' => true, 'msg' => 'Empresa actualizada correctamente.', 'data' => $updated[0] ?? null]);
    }
}
