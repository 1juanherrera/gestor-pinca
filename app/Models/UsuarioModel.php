<?php
namespace App\Models;

class UsuarioModel extends BaseModel
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuarios';

    protected $allowedFields = [
        'username',
        'nombre',
        'password',
        'rol',
        'password_must_change',
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    public function __construct(){
        parent::__construct();
    }

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }
        return $data;
    }
}
