<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InstalacionesModel;

class InstalacionesController extends ResourceController 
{
    public function __construct(){

        $this->model = new InstalacionesModel();
    }

    public function instalaciones(){
        $instalaciones = $this->model->get_all('instalaciones');
        return $this->respond($instalaciones);
    }

    public function instalaciones_with_bodegas() {
        $data = $this->model->instalaciones_with_bodegas();
        return $this->respond($data);
    }
}