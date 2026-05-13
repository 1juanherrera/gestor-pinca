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
        'ciudad',
        'plazo_pago',
        'telefono',
        'email',
        'tipo',
        'estado',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // get() heredado de BaseModel — respeta soft-deletes automáticamente

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

    // Clientes con saldo pendiente total y días en mora
    public function get_item_clientes($id = null): array
    {
        $sql = "
            SELECT
                c.id_clientes,
                c.nombre_encargado,
                c.nombre_empresa,
                c.numero_documento,
                c.direccion,
                c.ciudad,
                c.plazo_pago,
                c.telefono,
                c.email,
                c.tipo,
                c.estado,
                COALESCE(SUM(f.saldo_pendiente), 0)                         AS saldo_pendiente,
                COUNT(CASE WHEN f.estado IN ('Pendiente','Parcial','Vencida') THEN 1 END) AS facturas_pendientes,
                MAX(CASE
                    WHEN f.fecha_vencimiento < CURDATE()
                     AND f.estado IN ('Pendiente','Parcial','Vencida')
                    THEN DATEDIFF(CURDATE(), f.fecha_vencimiento)
                    ELSE 0
                END)                                                          AS dias_mora_max
            FROM clientes c
            LEFT JOIN facturas f ON f.cliente_id = c.id_clientes
                AND f.estado IN ('Pendiente','Parcial','Vencida')
        ";

        $params = [];
        if ($id !== null) {
            $sql .= " WHERE c.id_clientes = ? ";
            $params[] = (int)$id;
        }

        $sql .= " GROUP BY c.id_clientes ORDER BY c.nombre_empresa ASC ";

        $rows = $this->db->query($sql, $params)->getResult();

        return array_map(function ($row) {
            return [
                'id_clientes'        => (int)   $row->id_clientes,
                'nombre_encargado'   =>          $row->nombre_encargado,
                'nombre_empresa'     =>          $row->nombre_empresa,
                'numero_documento'   =>          $row->numero_documento,
                'direccion'          =>          $row->direccion,
                'ciudad'             =>          $row->ciudad,
                'plazo_pago'         => (int)    $row->plazo_pago,
                'telefono'           =>          $row->telefono,
                'email'              =>          $row->email,
                'tipo'               => (int)    $row->tipo,
                'estado'             => (int)    $row->estado,
                'saldo_pendiente'    => (float)  $row->saldo_pendiente,
                'facturas_pendientes'=> (int)    $row->facturas_pendientes,
                'dias_mora_max'      => (int)    $row->dias_mora_max,
                'en_mora'            =>          ((int)$row->dias_mora_max) > 0,
            ];
        }, $rows);
    }
}