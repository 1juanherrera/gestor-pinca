<?php

namespace App\Models;
use App\Libraries\Formatter;

class ClientesModel extends BaseModel
{

    protected $table = 'clientes';
    protected $primaryKey = 'id_clientes';
    protected $allowedFields = [
        'nombre_encargado',
        'nombre_empresa',
        'numero_documento',
        'direccion',
        'telefono',
        'email',
        'tipo',
        'estado',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function get($id, $table)
    {
        $this->table = $table;
        return $this->find($id);
    }

    public function create_cliente($data, $table)
    {
        $this->table = $table;      
        return $this->insert($data);
    }

    public function update_cliente($id, $data, $table)
    {
        $this->table = $table;      
        return $this->update($id, $data);
    }

    public function delete_cliente($id, $table)
    {
        $this->table = $table;      
        return $this->delete($id);
    }
}