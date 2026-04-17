<?php

namespace App\Models;

use Exception;

class TamborModel extends BaseModel
{
    protected $table      = 'tambores';
    protected $primaryKey = 'id_tambor';
    protected $allowedFields = [
        'numero_tambor',
        'item_general_id',
        'bodegas_id',
        'cantidad_inicial',
        'cantidad_actual',
        'estado',
        'fecha_ingreso',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function get_tambores(array $filters = []): array
    {
        $builder = $this->db->table('tambores t')
            ->select('t.*, ig.nombre AS material, ig.codigo AS codigo_material, b.nombre AS bodega')
            ->join('item_general ig', 'ig.id_item_general = t.item_general_id')
            ->join('bodegas b', 'b.id_bodegas = t.bodegas_id')
            ->orderBy('t.numero_tambor', 'ASC');

        if (!empty($filters['item_general_id'])) {
            $builder->where('t.item_general_id', $filters['item_general_id']);
        }
        if (!empty($filters['bodegas_id'])) {
            $builder->where('t.bodegas_id', $filters['bodegas_id']);
        }
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $builder->where('t.estado', $filters['estado']);
        }
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('t.numero_tambor', $filters['search'])
                ->orLike('ig.nombre', $filters['search'])
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function get_tambor_detalle(int $id): ?array
    {
        $tambor = $this->db->table('tambores t')
            ->select('t.*, ig.nombre AS material, ig.codigo AS codigo_material, b.nombre AS bodega')
            ->join('item_general ig', 'ig.id_item_general = t.item_general_id')
            ->join('bodegas b', 'b.id_bodegas = t.bodegas_id')
            ->where('t.id_tambor', $id)
            ->get()->getRowArray();

        if (!$tambor) return null;

        $tambor['movimientos'] = $this->db->table('tambor_movimientos')
            ->where('tambor_id', $id)
            ->orderBy('fecha', 'DESC')
            ->get()->getResultArray();

        return $tambor;
    }

    /**
     * Crea uno o varios tambores a partir de una lista de números separados por coma.
     * Retorna los IDs creados.
     */
    public function crear_tambores(array $data): array
    {
        $numeros   = array_filter(array_map('trim', explode(',', $data['numeros'] ?? '')));
        $fecha     = $data['fecha_ingreso'] ?? date('Y-m-d');
        $ids       = [];

        $this->db->transStart();

        foreach ($numeros as $numero) {
            $id = $this->insert([
                'numero_tambor'    => $numero,
                'item_general_id'  => $data['item_general_id'],
                'bodegas_id'       => $data['bodegas_id'],
                'cantidad_inicial' => $data['cantidad_inicial'],
                'cantidad_actual'  => $data['cantidad_inicial'],
                'estado'           => 0,
                'fecha_ingreso'    => $fecha,
            ]);

            $this->db->table('tambor_movimientos')->insert([
                'tambor_id'       => $id,
                'tipo'            => 1,
                'cantidad'        => $data['cantidad_inicial'],
                'referencia_tipo' => 'INGRESO',
                'referencia_id'   => null,
                'fecha'           => $fecha,
            ]);

            $ids[] = $id;
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new Exception('Error al crear los tambores.');
        }

        return $ids;
    }

    public function consumir_tambor(int $id, float $cantidad, string $referencia_tipo, ?int $referencia_id): array
    {
        $tambor = $this->find($id);
        if (!$tambor) {
            throw new Exception("Tambor con ID $id no encontrado.");
        }
        if ($tambor['estado'] == 2) {
            throw new Exception("El tambor $id ya está vacío.");
        }
        if ($cantidad > $tambor['cantidad_actual']) {
            throw new Exception("Cantidad a consumir ({$cantidad}) supera el disponible ({$tambor['cantidad_actual']}).");
        }

        $nueva_cantidad = round($tambor['cantidad_actual'] - $cantidad, 2);
        $nuevo_estado   = $nueva_cantidad <= 0 ? 2 : 1;

        $this->db->transStart();

        $this->update($id, [
            'cantidad_actual' => $nueva_cantidad,
            'estado'          => $nuevo_estado,
        ]);

        $this->db->table('tambor_movimientos')->insert([
            'tambor_id'       => $id,
            'tipo'            => 2,
            'cantidad'        => $cantidad,
            'referencia_tipo' => $referencia_tipo,
            'referencia_id'   => $referencia_id,
            'fecha'           => date('Y-m-d'),
        ]);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new Exception('Error al registrar el consumo del tambor.');
        }

        return $this->get_tambor_detalle($id);
    }

    public function get_tambores_disponibles(int $item_general_id, ?int $bodegas_id = null): array
    {
        $builder = $this->db->table('tambores t')
            ->select('t.*, b.nombre AS bodega')
            ->join('bodegas b', 'b.id_bodegas = t.bodegas_id')
            ->where('t.item_general_id', $item_general_id)
            ->where('t.estado !=', 2)
            ->orderBy('t.estado', 'ASC')
            ->orderBy('t.numero_tambor', 'ASC');

        if ($bodegas_id) {
            $builder->where('t.bodegas_id', $bodegas_id);
        }

        return $builder->get()->getResultArray();
    }
}
