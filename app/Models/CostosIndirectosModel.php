<?php
namespace App\Models;

class CostosIndirectosModel extends BaseModel
{
    protected $table      = 'costos_indirectos';
    protected $primaryKey = 'id_costos_indirectos';

    protected $allowedFields = [
        'nombre',
        'categoria',
        'valor_mensual',
        'activo',
        'fecha_actualizacion',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // ── Listado de costos indirectos activos ──────────────────────────────────
    public function listar(): array
    {
        return $this->db->query("
            SELECT *
            FROM costos_indirectos
            ORDER BY categoria ASC, nombre ASC
        ")->getResultArray();
    }

    // ── Total mensual agrupado por categoría ──────────────────────────────────
    public function resumen(): array
    {
        $rows = $this->db->query("
            SELECT
                categoria,
                SUM(valor_mensual) AS total,
                COUNT(*)           AS cantidad
            FROM costos_indirectos
            WHERE activo = 1
            GROUP BY categoria
            ORDER BY categoria ASC
        ")->getResultArray();

        $totalGeneral = array_sum(array_column($rows, 'total'));

        return [
            'por_categoria'  => $rows,
            'total_mensual'  => (float) $totalGeneral,
        ];
    }

    // ── Upsert de asignación a ítem ───────────────────────────────────────────
    public function asignarAItem(int $itemId, int $costoId, float $valor): bool
    {
        $existe = $this->db->query("
            SELECT id FROM costos_indirectos_item
            WHERE item_general_id = ? AND costos_indirectos_id = ?
        ", [$itemId, $costoId])->getRow();

        if ($existe) {
            $this->db->query("
                UPDATE costos_indirectos_item
                SET valor_asignado = ?
                WHERE item_general_id = ? AND costos_indirectos_id = ?
            ", [$valor, $itemId, $costoId]);
        } else {
            $this->db->query("
                INSERT INTO costos_indirectos_item (item_general_id, costos_indirectos_id, valor_asignado)
                VALUES (?, ?, ?)
            ", [$itemId, $costoId, $valor]);
        }

        return true;
    }

    // ── Costos asignados a un ítem ────────────────────────────────────────────
    public function costosDeItem(int $itemId): array
    {
        return $this->db->query("
            SELECT
                ci.id_costos_indirectos,
                ci.nombre,
                ci.categoria,
                ci.valor_mensual,
                COALESCE(cii.valor_asignado, 0) AS valor_asignado
            FROM costos_indirectos ci
            LEFT JOIN costos_indirectos_item cii
                ON cii.costos_indirectos_id = ci.id_costos_indirectos
                AND cii.item_general_id = ?
            WHERE ci.activo = 1
            ORDER BY ci.categoria ASC, ci.nombre ASC
        ", [$itemId])->getResultArray();
    }

    // ── Total de costos indirectos asignados a un ítem ────────────────────────
    public function totalAsignadoItem(int $itemId): float
    {
        $row = $this->db->query("
            SELECT COALESCE(SUM(cii.valor_asignado), 0) AS total
            FROM costos_indirectos_item cii
            INNER JOIN costos_indirectos ci ON ci.id_costos_indirectos = cii.costos_indirectos_id
            WHERE cii.item_general_id = ? AND ci.activo = 1
        ", [$itemId])->getRow();

        return (float) ($row->total ?? 0);
    }
}
