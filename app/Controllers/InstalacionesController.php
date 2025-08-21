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

    public function bodegas($id){
        $bodegas = $this->model->get_bodegas_by_instalacion($id);
        if ($bodegas) {
            return $this->respond($bodegas);
        } else {
            return $this->failNotFound('No se encontraron bodegas para la instalación especificada.');
        }
    }
}