<?php

namespace App\Models;

class CategoriaModel extends BaseModel
{

    protected $table = 'categoria';
    protected $primaryKey = 'id_categoria';
    protected $allowedFields = [
        "nombre",
    ];

    public function __construct()
    {
        parent::__construct();
    }
}