<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoVerificacion extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_verificacion';

    protected $fillable = ['disco','ruta','nombre_original','mime_type','extension','tamano','hash_sha256','tipo_documento','estado_verificacion_id','user_id','empresa_id','verificacion_usuario_id','verificacion_empresa_id','subido_por','revisado_por','fecha_revision'];

    protected function casts(): array
    {
        return ['tamano' => 'integer', 'fecha_revision' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function empresa(): BelongsTo { return $this->belongsTo(Empresa::class, 'empresa_id'); }
    public function verificacionUsuario(): BelongsTo { return $this->belongsTo(VerificacionUsuario::class, 'verificacion_usuario_id'); }
    public function verificacionEmpresa(): BelongsTo { return $this->belongsTo(VerificacionEmpresa::class, 'verificacion_empresa_id'); }
    public function estadoVerificacion(): BelongsTo { return $this->belongsTo(EstadoVerificacion::class, 'estado_verificacion_id'); }
    public function subidoPor(): BelongsTo { return $this->belongsTo(User::class, 'subido_por'); }
    public function revisadoPor(): BelongsTo { return $this->belongsTo(User::class, 'revisado_por'); }
}
