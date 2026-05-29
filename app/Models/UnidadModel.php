<?php

namespace App\Models;

class UnidadModel extends BaseModel
{

    protected $table = 'unidad';
    protected $primaryKey = 'id_unidad';
    protected $allowedFields = [
        "numero",
        "nombre",
        "descripcion",
        "estados",
        "escala"
    ];

    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    public function __construct()
    {
        parent::__construct();
    }
}