<?php

namespace App\Controllers;

use App\Models\PreparacionesModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class PreparacionesController extends BaseController
{
    private PreparacionesModel $model;

    public function __construct()
    {
        $this->model = new PreparacionesModel();
    }

    /**
     * GET /preparaciones
     * Lista todas las preparaciones (paginado opcional con ?page=1&limit=20)
     */
    public function index(): ResponseInterface
    {
        try {
            $page  = (int) ($this->request->getGet('page')  ?? 1);
            $limit = (int) ($this->request->getGet('limit') ?? 20);
            $result = $this->model->get_all_preparaciones($page, $limit);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /preparaciones
     * Crea una nueva orden de preparación.
     */
    public function create(): ResponseInterface
    {
        $body = $this->request->getJSON(true) ?? $this->request->getPost();

        try {
            $result = $this->model->create_preparacion($body);
            return $this->response
                ->setStatusCode(201)
                ->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /preparaciones/(:num)
     * Detalle de una preparación con su desglose de materias primas.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $result = $this->model->get_preparacion_by_id($id);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /preparaciones/item/(:num)
     * Lista todas las preparaciones de un item específico.
     */
    public function byItem(int $itemId): ResponseInterface
    {
        try {
            $result = $this->model->get_preparaciones_by_item($itemId);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * PUT /preparaciones/(:num)
     * Actualiza estado u observaciones de una preparación.
     */
    public function update(int $id): ResponseInterface
    {
        $body = $this->request->getJSON(true) ?? $this->request->getRawInput();

        try {
            $result = $this->model->update_preparacion($id, $body);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /preparaciones/(:num)/costos
     * Agrega un costo indirecto a una preparación.
     */
    public function addCosto(int $id): ResponseInterface
    {
        $body = $this->request->getJSON(true);
        $nombre    = trim($body['nombre']    ?? '');
        $categoria = trim($body['categoria'] ?? 'otros');
        $valor     = (float) ($body['valor_aplicado'] ?? 0);

        if (!$nombre || $valor <= 0) {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'nombre y valor_aplicado son obligatorios.']);
        }

        try {
            $result = $this->model->add_costo_indirecto($id, $nombre, $categoria, $valor);
            return $this->response->setStatusCode(201)
                ->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * PUT /preparaciones/(:num)/costos/(:num)
     * Edita nombre, categoría o valor de un costo indirecto.
     */
    public function updateCosto(int $prepId, int $costoId): ResponseInterface
    {
        $body = $this->request->getJSON(true);
        try {
            $this->model->update_costo_indirecto($costoId, $body);
            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /preparaciones/(:num)/costos/(:num)
     * Elimina un costo indirecto de una preparación.
     */
    public function deleteCosto(int $prepId, int $costoId): ResponseInterface
    {
        try {
            $this->model->delete_costo_indirecto($costoId);
            return $this->respond(['success' => true]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /preparaciones/costos_resumen
     * Retorna lista de preparaciones con costos agregados (MP + indirectos).
     * Query params:
     *   - desde      (YYYY-MM-DD)  — inicio del período (default: 1º día del mes actual)
     *   - hasta      (YYYY-MM-DD)  — fin del período    (default: hoy)
     *   - estado     (int)         — filtrar por estado 0/1/2/3 (opcional)
     */
    public function costosResumen(): ResponseInterface
    {
        try {
            $db     = \Config\Database::connect();
            $desde  = $this->request->getGet('desde') ?? date('Y-m-01');
            $hasta  = $this->request->getGet('hasta') ?? date('Y-m-d');
            $estado = $this->request->getGet('estado');

            // Subquery: costo MP por preparación
            // Usa el costo_unitario más reciente (mayor id) por materia prima
            $sql = "
                SELECT
                    p.id_preparaciones,
                    p.fecha_creacion,
                    CASE p.estado
                        WHEN 0 THEN 'PENDIENTE'
                        WHEN 1 THEN 'EN_PROCESO'
                        WHEN 2 THEN 'COMPLETADA'
                        WHEN 3 THEN 'CANCELADA'
                    END AS estado,
                    ig.nombre        AS item_nombre,
                    ig.codigo        AS item_codigo,
                    p.cantidad,
                    u.nombre         AS unidad,
                    COALESCE(mp.costo_mp, 0)             AS costo_mp_total,
                    COALESCE(ci_agg.costo_indirectos, 0) AS costo_indirectos_total,
                    (COALESCE(mp.costo_mp, 0) + COALESCE(ci_agg.costo_indirectos, 0)) AS costo_total
                FROM preparaciones p
                JOIN item_general ig ON ig.id_item_general = p.item_general_id
                JOIN unidad u        ON u.id_unidad        = p.unidad_id
                LEFT JOIN (
                    SELECT
                        phig.preparaciones_id_preparaciones,
                        SUM(phig.cantidad * COALESCE(ci_latest.costo_unitario, 0)) AS costo_mp
                    FROM preparaciones_has_item_general phig
                    LEFT JOIN (
                        SELECT ci1.item_general_id, ci1.costo_unitario
                        FROM costos_item ci1
                        INNER JOIN (
                            SELECT item_general_id, MAX(id_costos_item) AS max_id
                            FROM costos_item
                            GROUP BY item_general_id
                        ) ci_max ON ci_max.item_general_id = ci1.item_general_id
                                 AND ci_max.max_id = ci1.id_costos_item
                    ) ci_latest ON ci_latest.item_general_id = phig.item_general_id
                    GROUP BY phig.preparaciones_id_preparaciones
                ) mp ON mp.preparaciones_id_preparaciones = p.id_preparaciones
                LEFT JOIN (
                    SELECT preparaciones_id, SUM(valor_aplicado) AS costo_indirectos
                    FROM preparaciones_costos_indirectos
                    GROUP BY preparaciones_id
                ) ci_agg ON ci_agg.preparaciones_id = p.id_preparaciones
                WHERE DATE(p.fecha_creacion) BETWEEN ? AND ?
            ";

            $params = [$desde, $hasta];

            if ($estado !== null && $estado !== '') {
                $sql    .= ' AND p.estado = ?';
                $params[] = (int) $estado;
            }

            $sql .= ' ORDER BY p.fecha_creacion DESC';

            $rows = $db->query($sql, $params)->getResultArray();

            $totalMp          = array_sum(array_column($rows, 'costo_mp_total'));
            $totalIndirectos  = array_sum(array_column($rows, 'costo_indirectos_total'));

            return $this->response->setJSON([
                'success' => true,
                'resumen' => [
                    'total_mp'          => (float) $totalMp,
                    'total_indirectos'  => (float) $totalIndirectos,
                    'gran_total'        => (float) ($totalMp + $totalIndirectos),
                    'cantidad_ordenes'  => count($rows),
                ],
                'data' => $rows,
            ]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}