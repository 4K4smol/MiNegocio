<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class DeclaracionResponsableSoftware extends Model
{
    protected $table = 'declaraciones_responsables_software';

    protected $fillable = [
        'nombre_software',
        'codigo_software',
        'version_software',
        'productor_nombre',
        'productor_nif',
        'productor_direccion',
        'descripcion',
        'componentes',
        'modo_verifactu',
        'permite_multiples_obligados',
        'fecha_declaracion',
        'lugar_declaracion',
        'texto_declaracion',
        'hash_declaracion',
        'pdf_path',
        'activa',
        'metadatos',
    ];

    protected $casts = [
        'componentes' => 'array',
        'modo_verifactu' => 'boolean',
        'permite_multiples_obligados' => 'boolean',
        'fecha_declaracion' => 'date',
        'activa' => 'boolean',
        'metadatos' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new RuntimeException('No se pueden borrar declaraciones responsables de software.'));
    }
}
