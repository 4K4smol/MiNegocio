<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroFacturacion extends Model
{
    protected $table = 'registros_facturacion';

    protected $fillable = [
        'factura_id','tipo_registro_facturacion_id','modo_remision_facturacion_id','empresa_id','emisor_nif',
        'emisor_nombre_razon_social','serie','numero','fecha_expedicion','tipo_factura_id','cuota_total','importe_total',
        'primer_registro_cadena','registro_anterior_nif','registro_anterior_serie','registro_anterior_numero',
        'registro_anterior_fecha_expedicion','registro_anterior_hash_64','tipo_huella','hash_actual','firma_electronica',
        'generado_at','xml_contenido','xml_version','codigo_sistema_informatico','id_sistema_informatico','nombre_sistema',
        'version_sistema','numero_instalacion','tipo_uso_posible_solo_verifactu','tipo_uso_posible_multi_ot','indicador_multiples_ot',
        'productor_nif','productor_nombre','enviado_aeat_at','respuesta_aeat',
    ];

    protected $casts = [
        'fecha_expedicion' => 'date',
        'cuota_total' => 'decimal:2',
        'importe_total' => 'decimal:2',
        'primer_registro_cadena' => 'boolean',
        'tipo_uso_posible_solo_verifactu' => 'boolean',
        'tipo_uso_posible_multi_ot' => 'boolean',
        'indicador_multiples_ot' => 'boolean',
        'generado_at' => 'datetime',
        'enviado_aeat_at' => 'datetime',
    ];

    public function factura(): BelongsTo { return $this->belongsTo(Factura::class, 'factura_id'); }
    public function tipoRegistroFacturacion(): BelongsTo { return $this->belongsTo(TipoRegistroFacturacion::class, 'tipo_registro_facturacion_id'); }
    public function modoRemisionFacturacion(): BelongsTo { return $this->belongsTo(ModoRemisionFacturacion::class, 'modo_remision_facturacion_id'); }
    public function empresa(): BelongsTo { return $this->belongsTo(Empresa::class, 'empresa_id'); }
    public function tipoFactura(): BelongsTo { return $this->belongsTo(TipoFactura::class, 'tipo_factura_id'); }
}
