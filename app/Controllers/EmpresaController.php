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

        // Asegurar que la respuesta siempre sea un array
        if (!is_array($empresa)) {
            $empresa = [$empresa];
        }

        return $this->respond($empresa);
    }
}