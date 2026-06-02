<?php

namespace App\Services;

/**
 * Clasifica materias primas / insumos por IDENTIDAD QUÍMICA usando una IA con un
 * system prompt de "químico experto en pinturas".
 *
 * Soporta dos proveedores (auto-detectado por la API key presente en .env):
 *   - Gemini (Google)   → GEMINI_API_KEY   (+ GEMINI_MODEL,   default gemini-2.0-flash)
 *   - Claude (Anthropic)→ ANTHROPIC_API_KEY (+ ANTHROPIC_MODEL, default claude-sonnet-4-6)
 *
 * Usa el servicio nativo de CI4 `curlrequest` (sin dependencias nuevas). Si no hay
 * ninguna key, lanza excepción (el flujo la captura y sugiere el modo offline del
 * command sync:clasificar).
 */
class ClasificadorQuimicoService
{
    private string $provider; // 'gemini' | 'anthropic'
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $gemini    = (string) (env('GEMINI_API_KEY') ?: getenv('GEMINI_API_KEY') ?: '');
        $anthropic = (string) (env('ANTHROPIC_API_KEY') ?: getenv('ANTHROPIC_API_KEY') ?: '');

        if ($gemini !== '') {
            $this->provider = 'gemini';
            $this->apiKey   = $gemini;
            $this->model    = (string) (env('GEMINI_MODEL') ?: 'gemini-2.0-flash');
        } elseif ($anthropic !== '') {
            $this->provider = 'anthropic';
            $this->apiKey   = $anthropic;
            $this->model    = (string) (env('ANTHROPIC_MODEL') ?: 'claude-sonnet-4-6');
        } else {
            throw new \RuntimeException('Falta GEMINI_API_KEY (o ANTHROPIC_API_KEY) en .env. Usá el modo offline: php spark sync:clasificar --offline');
        }
    }

    public function modelo(): string
    {
        return $this->provider . ':' . $this->model;
    }

    private const SYSTEM_PROMPT = <<<'TXT'
Eres un químico experto en formulación de pinturas, recubrimientos y materias primas
industriales. Recibes un listado de materias primas/insumos de un ERP, cada uno con su
nombre, categoría, unidad, y los nombres técnicos/marcas con que distintos proveedores
lo venden. Tu tarea es AGRUPAR los que son EL MISMO material químico funcional, aunque
tengan nombres comerciales, marcas o referencias distintas.

REGLAS:
- Agrupa por IDENTIDAD QUÍMICA FUNCIONAL. Ej: "Dióxido de titanio rutilo" agrupa TiO2,
  Ti-Pure R-902, Tioxide, Kronos 2310, etc.
- NO agrupes grados/calidades distintas que cambian la fórmula como si fueran idénticos
  (rutilo vs anatasa; resina al 50% vs 100% de sólidos; talco industrial vs farmacéutico).
  Si dudas de la equivalencia, marca confianza "baja" y explica el motivo de verificación.
- NO agrupes materiales con FUNCIÓN distinta aunque el nombre se parezca (dispersante vs
  espesante; biocida vs fungicida específico).
- Propón un NOMBRE BASE limpio, genérico, en español, SIN marca ni proveedor ni código
  (ej. "Dióxido de titanio rutilo", "Caolín calcinado", "Dispersante poliacrílico").
- Elige keep_id = el id del miembro con más proveedores o más stock (el más "canónico").
- Devuelve confianza por grupo Y por ítem ("alta" | "media" | "baja"). Marca "baja" en
  ítems dudosos con motivo (ej. "verificar grado con ficha técnica del proveedor").
- SOLO incluye grupos con 2 o más miembros (duplicados reales). Los ítems únicos se omiten.
- Un grupo NUNCA mezcla tipos distintos (no juntes una Materia Prima con un Insumo).

Responde EXCLUSIVAMENTE con JSON válido, sin texto antes ni después, con esta forma:
{
  "clusters": [
    {
      "identidad_quimica": "Dióxido de titanio rutilo",
      "nombre_base": "Dióxido de titanio rutilo",
      "clave_grupo": "dioxido-titanio-rutilo",
      "confianza": "alta",
      "razonamiento": "Todos son TiO2 grado rutilo de distintas marcas...",
      "tipo": 1,
      "keep_id": 123,
      "items": [
        {"item_general_id": 123, "confianza": "alta", "motivo": null},
        {"item_general_id": 456, "confianza": "media", "motivo": "marca distinta, misma función"}
      ]
    }
  ]
}
TXT;

    /**
     * Clasifica el dataset completo, troceando en lotes.
     *
     * @param array $dataset salida de SincronizacionModel::datasetParaClasificacion
     * @return array clusters
     */
    public function clasificar(array $dataset, int $batchSize = 100): array
    {
        $clusters = [];
        $lotes = array_chunk($dataset, max(20, $batchSize));
        foreach ($lotes as $i => $lote) {
            $parsed = $this->clasificarLote($lote, $i + 1, count($lotes));
            foreach (($parsed['clusters'] ?? []) as $c) {
                $clusters[] = $c;
            }
        }
        return $clusters;
    }

    private function clasificarLote(array $lote, int $n, int $total): array
    {
        // Compactar para minimizar tokens.
        $compacto = array_map(function ($it) {
            $refs = array_map(fn($r) => trim(($r['nombre_tecnico'] ?? '') . ' [' . ($r['proveedor'] ?? '') . ']'),
                $it['referencias_proveedor'] ?? []);
            return [
                'id'        => $it['id_item_general'],
                'nombre'    => $it['nombre'],
                'tipo'      => $it['tipo'],
                'categoria' => $it['categoria'] ?? null,
                'refs'      => $refs,
                'usos'      => $it['usos_en_formulas'] ?? 0,
            ];
        }, $lote);

        $userMsg = "Lote {$n} de {$total}. Clasifica estos ítems (cada uno es un item_general "
            . "del ERP; agrupa los que sean el mismo material químico):\n\n"
            . json_encode($compacto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $text = $this->provider === 'gemini'
            ? $this->callGemini($userMsg)
            : $this->callAnthropic($userMsg);

        return $this->extraerJson($text);
    }

    private function callGemini(string $userMsg): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
        $body = [
            'systemInstruction' => ['parts' => [['text' => self::SYSTEM_PROMPT]]],
            'contents'          => [['role' => 'user', 'parts' => [['text' => $userMsg]]]],
            'generationConfig'  => [
                'responseMimeType' => 'application/json',
                'temperature'      => 0.2,
                'maxOutputTokens'  => 32768, // headroom: 2.5-flash usa "thinking" + el JSON de salida
            ],
        ];

        $json = $this->post($url, $body, []);
        $text = '';
        foreach (($json['candidates'][0]['content']['parts'] ?? []) as $p) {
            $text .= $p['text'] ?? '';
        }
        if ($text === '') {
            $reason = $json['candidates'][0]['finishReason'] ?? ($json['error']['message'] ?? 'sin contenido');
            throw new \RuntimeException('Gemini no devolvió texto (' . $reason . ').');
        }
        return $text;
    }

    private function callAnthropic(string $userMsg): string
    {
        $body = [
            'model'      => $this->model,
            'max_tokens' => 16000,
            'system'     => [[
                'type'          => 'text',
                'text'          => self::SYSTEM_PROMPT,
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'messages'   => [['role' => 'user', 'content' => $userMsg]],
        ];
        $json = $this->post('https://api.anthropic.com/v1/messages', $body, [
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ]);
        $text = '';
        foreach (($json['content'] ?? []) as $blk) {
            if (($blk['type'] ?? '') === 'text') $text .= $blk['text'];
        }
        if ($text === '') throw new \RuntimeException('Anthropic no devolvió texto.');
        return $text;
    }

    /** POST JSON con reintento; devuelve el body decodificado. Nunca loguea la URL (lleva la key). */
    private function post(string $url, array $body, array $headers, int $intentos = 2): array
    {
        $client = \Config\Services::curlrequest();
        $ultimoError = '';
        for ($i = 0; $i < $intentos; $i++) {
            try {
                $resp = $client->post($url, [
                    'headers'     => array_merge(['content-type' => 'application/json'], $headers),
                    'body'        => json_encode($body),
                    'http_errors' => false,
                    'timeout'     => 180,
                ]);
                $status = $resp->getStatusCode();
                $json   = json_decode($resp->getBody(), true);
                if ($status >= 200 && $status < 300 && is_array($json)) {
                    return $json;
                }
                $ultimoError = 'HTTP ' . $status . ': ' . (is_array($json) ? ($json['error']['message'] ?? '') : substr((string) $resp->getBody(), 0, 200));
            } catch (\Throwable $e) {
                $ultimoError = $e->getMessage();
            }
        }
        throw new \RuntimeException('Error llamando a la IA (' . $this->provider . '): ' . $ultimoError);
    }

    private function extraerJson(string $text): array
    {
        $t = trim($text);
        $t = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $t);
        $data = json_decode($t, true);
        if (is_array($data)) return $data;
        $start = strpos($t, '{');
        $end   = strrpos($t, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $data = json_decode(substr($t, $start, $end - $start + 1), true);
            if (is_array($data)) return $data;
        }
        throw new \RuntimeException('La IA no devolvió JSON válido.');
    }
}
