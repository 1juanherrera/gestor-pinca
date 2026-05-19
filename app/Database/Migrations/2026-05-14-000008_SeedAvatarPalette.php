<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\ConfiguracionModel;

/**
 * Seed para la paleta de avatares (apariencia).
 * El frontend lee este JSON desde `useAvatarPalette()` y cae al hardcoded
 * si la clave no existe.
 */
class SeedAvatarPalette extends Migration
{
    public function up()
    {
        $palette = [
            ['key' => 'default', 'name' => 'Por rol',   'grad' => null,                              'preview' => 'from-zinc-400  to-zinc-600'    ],
            ['key' => 'violet',  'name' => 'Violeta',   'grad' => 'from-violet-500  to-purple-600',  'preview' => 'from-violet-500 to-purple-600' ],
            ['key' => 'blue',    'name' => 'Azul',      'grad' => 'from-blue-500    to-cyan-600',    'preview' => 'from-blue-500   to-cyan-600'   ],
            ['key' => 'emerald', 'name' => 'Esmeralda', 'grad' => 'from-emerald-500 to-teal-600',    'preview' => 'from-emerald-500 to-teal-600'  ],
            ['key' => 'amber',   'name' => 'Ámbar',     'grad' => 'from-amber-500   to-orange-600',  'preview' => 'from-amber-500  to-orange-600' ],
            ['key' => 'rose',    'name' => 'Rosa',      'grad' => 'from-rose-500    to-pink-600',    'preview' => 'from-rose-500   to-pink-600'   ],
            ['key' => 'slate',   'name' => 'Pizarra',   'grad' => 'from-slate-600   to-zinc-800',    'preview' => 'from-slate-600  to-zinc-800'   ],
            ['key' => 'indigo',  'name' => 'Índigo',    'grad' => 'from-indigo-500  to-fuchsia-600', 'preview' => 'from-indigo-500 to-fuchsia-600'],
        ];

        $cfg = new ConfiguracionModel();
        $cfg->seedIfMissing(
            'apariencia',
            'avatar_palette',
            $palette,
            'json',
            'Paleta de gradientes que cada usuario puede elegir para su avatar. Array JSON: [{key, name, grad, preview}].'
        );
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('configuracion_sistema')
           ->where('clave', 'avatar_palette')
           ->delete();
    }
}
