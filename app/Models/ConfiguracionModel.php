<?php
namespace App\Models;

/**
 * ConfiguracionModel — acceso a `configuracion_sistema`.
 *
 * El valor se almacena como JSON y se serializa/deserializa según `tipo`.
 * Tipos soportados: 'string' | 'number' | 'boolean' | 'json'
 */
class ConfiguracionModel extends BaseModel
{
    protected $table         = 'configuracion_sistema';
    protected $primaryKey    = 'id_configuracion';
    protected $allowedFields = ['grupo', 'clave', 'valor', 'tipo', 'descripcion', 'updated_at', 'updated_by'];

    /**
     * Lee un valor por clave; retorna $default si no existe.
     * (No usa el nombre `get` para no chocar con BaseModel::get($id, $table).)
     */
    public function obtener(string $clave, $default = null)
    {
        $row = $this->where('clave', $clave)->first();
        if (!$row) return $default;

        return $this->castValue($row['valor'], $row['tipo']);
    }

    /**
     * Guarda o actualiza un valor. Si la clave no existe la crea.
     * (Renombrado de `set` para no chocar con CI4 Model::set.)
     */
    public function guardar(string $clave, $valor, string $usuario = 'sistema'): bool
    {
        $existente = $this->where('clave', $clave)->first();

        $payload = [
            'valor'      => json_encode($valor, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $usuario,
        ];

        if ($existente) {
            return $this->update($existente['id_configuracion'], $payload) !== false;
        }

        // Inserción por primera vez sin grupo/tipo → defaults sanos
        $payload['clave']       = $clave;
        $payload['grupo']       = 'sistema';
        $payload['tipo']        = $this->detectTipo($valor);
        $payload['descripcion'] = null;

        return $this->insert($payload) !== false;
    }

    /**
     * Devuelve todas las claves de un grupo como diccionario { clave: valor_castado, … }.
     */
    public function getGrupo(string $grupo): array
    {
        $rows = $this->where('grupo', $grupo)->orderBy('clave')->findAll();
        $out  = [];
        foreach ($rows as $r) {
            $out[$r['clave']] = [
                'valor'       => $this->castValue($r['valor'], $r['tipo']),
                'tipo'        => $r['tipo'],
                'descripcion' => $r['descripcion'],
                'updated_at'  => $r['updated_at'],
                'updated_by'  => $r['updated_by'],
            ];
        }
        return $out;
    }

    /**
     * Todas las configuraciones agrupadas: { grupo: { clave: { valor, tipo, … } } }
     */
    public function getAllGrouped(): array
    {
        $rows = $this->orderBy('grupo')->orderBy('clave')->findAll();
        $out  = [];
        foreach ($rows as $r) {
            $out[$r['grupo']][$r['clave']] = [
                'valor'       => $this->castValue($r['valor'], $r['tipo']),
                'tipo'        => $r['tipo'],
                'descripcion' => $r['descripcion'],
                'updated_at'  => $r['updated_at'],
                'updated_by'  => $r['updated_by'],
            ];
        }
        return $out;
    }

    /**
     * Insert si no existe — útil para seeds idempotentes.
     */
    public function seedIfMissing(string $grupo, string $clave, $valor, string $tipo, ?string $descripcion = null): void
    {
        if ($this->where('clave', $clave)->countAllResults() > 0) return;

        $this->insert([
            'grupo'       => $grupo,
            'clave'       => $clave,
            'valor'       => json_encode($valor, JSON_UNESCAPED_UNICODE),
            'tipo'        => $tipo,
            'descripcion' => $descripcion,
            'updated_at'  => date('Y-m-d H:i:s'),
            'updated_by'  => 'seed',
        ]);
    }

    // ── helpers internos ────────────────────────────────────────────────────

    private function castValue(?string $valorJson, string $tipo)
    {
        if ($valorJson === null) return null;
        $decoded = json_decode($valorJson, true);

        return match ($tipo) {
            'number'  => is_numeric($decoded) ? $decoded + 0 : 0,
            'boolean' => (bool) $decoded,
            'json'    => $decoded,
            default   => (string) $decoded, // string
        };
    }

    private function detectTipo($valor): string
    {
        if (is_bool($valor))   return 'boolean';
        if (is_numeric($valor)) return 'number';
        if (is_array($valor) || is_object($valor)) return 'json';
        return 'string';
    }
}
