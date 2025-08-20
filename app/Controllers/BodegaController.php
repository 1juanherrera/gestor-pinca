<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BodegaModel;

class BodegaController extends ResourceController 
{
    public function __construct(){

        $this->model = new BodegaModel();
    }

    public function bodega()
    {
        $bodegas = $this->model->get_all('bodegas');
        return $this->respond($bodegas);
    }
}