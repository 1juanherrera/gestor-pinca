<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EmpresaModel;
use App\Traits\JwtUserAware;

class EmpresaController extends ResourceController
{
    use \App\Traits\ApiResponse;

    use JwtUserAware;

    protected $modelName = EmpresaModel::class;
    protected $format    = 'json';

    /** GET /api/empresa — devuelve la empresa única como objeto */
    public function empresa()
    {
        $empresa = $this->model->get_all('empresa');
        if (empty($empresa)) return $this->respond(null);
        return $this->respond($empresa[0]);
    }

    /** PUT /api/empresa — actualiza la empresa (solo admin) */
    public function update($id = null)
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo administradores pueden modificar la empresa.');
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        if (!$data) return $this->apiFail('Datos inválidos', 400);

        $empresa = $this->model->get_all('empresa');
        if (empty($empresa)) return $this->apiNotFound('No se encontró el registro de empresa.');

        $idEmpresa = $empresa[0]['id_empresa'];
        $allowed   = [
            'nit', 'razon_social', 'descripcion', 'ciudad', 'direccion',
            'telefono', 'celular', 'pagina_web', 'email',
            'locale', 'moneda', 'logo_path',
        ];
        $update = array_intersect_key($data, array_flip($allowed));

        if (empty($update)) return $this->apiFail('No se enviaron campos válidos.', 400);

        $this->model->update_table($idEmpresa, $update, 'empresa');

        $updated = $this->model->get_all('empresa');
        return $this->respond(['ok' => true, 'msg' => 'Empresa actualizada correctamente.', 'data' => $updated[0] ?? null]);
    }

    /**
     * POST /api/empresa/logo — sube/reemplaza el logo (solo admin).
     * Espera multipart/form-data con campo `logo` (PNG/JPG/WEBP, máx 2 MB).
     */
    public function uploadLogo()
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo administradores pueden cambiar el logo.');
        }

        $file = $this->request->getFile('logo');
        if (!$file || !$file->isValid()) {
            return $this->apiFail('No se recibió un archivo válido.', 422);
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return $this->apiFail('Formato no soportado. Usa PNG, JPG o WEBP.', 422);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->apiFail('El archivo excede 2 MB.', 422);
        }

        $empresa = $this->model->get_all('empresa');
        if (empty($empresa)) return $this->apiNotFound('No hay registro de empresa.');
        $idEmpresa = $empresa[0]['id_empresa'];

        $publicUploads = FCPATH . 'uploads/empresa';
        if (!is_dir($publicUploads)) mkdir($publicUploads, 0755, true);

        // Borrar logo previo
        $previo = $empresa[0]['logo_path'] ?? null;
        if ($previo) {
            $rutaPrevia = FCPATH . ltrim($previo, '/');
            if (is_file($rutaPrevia)) @unlink($rutaPrevia);
        }

        $nombre = 'logo_' . time() . '.' . $ext;
        if (!$file->move($publicUploads, $nombre)) {
            return $this->apiFail('No se pudo guardar el archivo.');
        }

        $logoPath = '/uploads/empresa/' . $nombre;
        $this->model->update_table($idEmpresa, ['logo_path' => $logoPath], 'empresa');

        return $this->respond([
            'ok'    => true,
            'msg'   => 'Logo actualizado correctamente.',
            'logo_path' => $logoPath,
        ]);
    }

    /**
     * GET /api/empresa/logo-base64 — devuelve el logo como data URI base64.
     *
     * Lo usa el frontend para incrustar el logo en PDFs generados con jsPDF
     * (evita problemas de CORS al hacer fetch directo a `/uploads/`).
     */
    public function logoBase64()
    {
        $empresa = $this->model->get_all('empresa');
        $logoPath = $empresa[0]['logo_path'] ?? null;
        if (!$logoPath) return $this->respond(['ok' => true, 'logo' => null]);

        $abs = FCPATH . ltrim($logoPath, '/');
        if (!is_file($abs)) return $this->respond(['ok' => true, 'logo' => null]);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $abs);
        finfo_close($finfo);
        $mime = $mime ?: 'image/png';
        $b64  = base64_encode(file_get_contents($abs));

        return $this->respond([
            'ok'   => true,
            'logo' => "data:{$mime};base64,{$b64}",
            'path' => $logoPath,
        ]);
    }

    /** DELETE /api/empresa/logo — quita el logo actual (solo admin). */
    public function deleteLogo()
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo administradores pueden eliminar el logo.');
        }

        $empresa = $this->model->get_all('empresa');
        if (empty($empresa)) return $this->apiNotFound('No hay registro de empresa.');
        $idEmpresa = $empresa[0]['id_empresa'];

        $previo = $empresa[0]['logo_path'] ?? null;
        if ($previo) {
            $rutaPrevia = FCPATH . ltrim($previo, '/');
            if (is_file($rutaPrevia)) @unlink($rutaPrevia);
        }

        $this->model->update_table($idEmpresa, ['logo_path' => null], 'empresa');
        return $this->respond(['ok' => true, 'msg' => 'Logo eliminado.']);
    }
}
