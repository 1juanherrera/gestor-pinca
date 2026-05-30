<?php
namespace App\Models;

class EmpresaModel extends BaseModel
{
    // Mass-assignment whitelist para la tabla `empresa`.
    // Nota: este modelo NO declara $table; las escrituras reales las hace
    // EmpresaController vía query builder directo. $allowedFields protege
    // un eventual save()/insert()/update() del propio modelo contra
    // mass-assignment. Columnas reales de `empresa` (incluye las agregadas
    // por la migración _000006_ExtendEmpresa).
    protected $allowedFields = [
        'nit',
        'razon_social',
        'descripcion',
        'ciudad',
        'direccion',
        'telefono',
        'celular',
        'pagina_web',
        'email',
        'locale',
        'moneda',
        'logo_path',
    ];

    public function __construct(){
        parent::__construct();
    }
}