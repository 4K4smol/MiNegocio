<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeclaracionResponsableSoftware;

class DeclaracionResponsableSoftwareService
{
    public function crear(array $data): DeclaracionResponsableSoftware
    {
        $fecha = (string) ($data['fecha_declaracion'] ?? now()->toDateString());
        $nombreSoftware = (string) ($data['nombre_software'] ?? config('app.name', 'MiNegocio'));
        $codigoSoftware = (string) ($data['codigo_software'] ?? 'MINEGOCIO-SIF');
        $versionSoftware = (string) ($data['version_software'] ?? config('app.version', '1.0.0'));
        $productorNombre = (string) ($data['productor_nombre'] ?? 'MiNegocio');
        $productorNif = $data['productor_nif'] ?? null;
        $productorDireccion = $data['productor_direccion'] ?? null;
        $descripcion = $data['descripcion'] ?? null;
        $componentes = $data['componentes'] ?? null;
        $modoVerifactu = (bool) ($data['modo_verifactu'] ?? false);
        $permiteMultiplesObligados = (bool) ($data['permite_multiples_obligados'] ?? true);
        $lugarDeclaracion = $data['lugar_declaracion'] ?? null;
        $texto = trim((string) ($data['texto_declaracion'] ?? ''));

        if ($texto === '') {
            $texto = $this->textoBase($nombreSoftware, $codigoSoftware, $versionSoftware);
        }

        $payloadHash = [
            'nombre_software' => $nombreSoftware,
            'codigo_software' => $codigoSoftware,
            'version_software' => $versionSoftware,
            'productor_nombre' => $productorNombre,
            'productor_nif' => $productorNif,
            'productor_direccion' => $productorDireccion,
            'descripcion' => $descripcion,
            'componentes' => $componentes,
            'modo_verifactu' => $modoVerifactu,
            'permite_multiples_obligados' => $permiteMultiplesObligados,
            'fecha_declaracion' => $fecha,
            'lugar_declaracion' => $lugarDeclaracion,
            'texto_declaracion' => $texto,
        ];

        return DeclaracionResponsableSoftware::query()->create([
            'nombre_software' => $nombreSoftware,
            'codigo_software' => $codigoSoftware,
            'version_software' => $versionSoftware,
            'productor_nombre' => $productorNombre,
            'productor_nif' => $productorNif,
            'productor_direccion' => $productorDireccion,
            'descripcion' => $descripcion,
            'componentes' => $componentes,
            'modo_verifactu' => $modoVerifactu,
            'permite_multiples_obligados' => $permiteMultiplesObligados,
            'fecha_declaracion' => $fecha,
            'lugar_declaracion' => $lugarDeclaracion,
            'texto_declaracion' => $texto,
            'hash_declaracion' => hash('sha256', json_encode($payloadHash, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
            'pdf_path' => $data['pdf_path'] ?? null,
            'activa' => (bool) ($data['activa'] ?? true),
            'metadatos' => $data['metadatos'] ?? null,
        ]);
    }

    private function textoBase(string $nombreSoftware, string $codigoSoftware, string $versionSoftware): string
    {
        return sprintf(
            'Declaracion responsable interna del sistema informatico de facturacion %s (%s), version %s. Este documento deja constancia tecnica del compromiso de trazabilidad, conservacion, integridad y legibilidad del flujo de facturacion implementado en el software.',
            $nombreSoftware,
            $codigoSoftware,
            $versionSoftware
        );
    }
}
