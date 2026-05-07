<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistroVerificacionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_crea_solicitud_pendiente(): void
    {
        Storage::fake('verificaciones');
        DB::table('estados_verificacion')->insert(['nombre'=>'pendiente']);
        DB::table('roles')->insert(['nombre'=>'titular']);
        DB::table('tipos_empresa')->insert(['nombre'=>'autonomo']);
        DB::table('tipos_documento_identidad')->insert(['nombre'=>'dni']);

        $response = $this->post('/api/v1/register', [
            'nombre' => 'Ana','apellido1' => 'Lopez','email' => 'ana@test.com','password' => 'Password123','password_confirmation' => 'Password123',
            'tipo_empresa_id' => 1,'nombre_fiscal' => 'Ana','nif' => 'B12345678','tipo_documento_identidad_id' => 1,'numero_documento' => '12345678A',
            'dni_frontal' => UploadedFile::fake()->create('dni.pdf', 100, 'application/pdf'),
        ]);

        $response->assertCreated();
    }
}
