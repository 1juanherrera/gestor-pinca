<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\bodegasModel;

class BodegasController extends ResourceController
{
    public function __construct(){

        $this->model = new BodegasModel();
    }

    public function bodegas()
    {
        $bodegas = $this->model->get_all('bodegas');
        return $this->respond($bodegas);
    }
}