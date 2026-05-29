<?php

namespace App\Models;

class CategoriaModel extends BaseModel
{

    protected $table = 'categoria';
    protected $primaryKey = 'id_categoria';
    protected $allowedFields = [
        "nombre",
    ];

    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    public function __construct()
    {
        parent::__construct();
    }
}