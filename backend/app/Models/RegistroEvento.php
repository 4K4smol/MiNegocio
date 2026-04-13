<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroEvento extends Model
{
    protected $table = 'registros_evento';

    protected $fillable = [
        'empresa_id','tipo_evento_facturacion_id','modo_remision_facturacion_id','codigo_evento','descripcion',
        'generado_at','hash_actual','hash_anterior_64','xml_contenido','xml_version',
    ];

    protected $casts = ['generado_at' => 'datetime'];

    public function empresa(): BelongsTo { return $this->belongsTo(Empresa::class, 'empresa_id'); }
    public function tipoEventoFacturacion(): BelongsTo { return $this->belongsTo(TipoEventoFacturacion::class, 'tipo_evento_facturacion_id'); }
    public function modoRemisionFacturacion(): BelongsTo { return $this->belongsTo(ModoRemisionFacturacion::class, 'modo_remision_facturacion_id'); }
}
