<?php
namespace App\Services;

use App\Models\BodegasModel;

class BodegasService
{
    protected $bodegasModel;

    public function __construct()
    {
        $this->bodegasModel = new BodegasModel();
    }

    public function getAllBodegas()
    {
        return $this->bodegasModel->findAll();
    }
}
