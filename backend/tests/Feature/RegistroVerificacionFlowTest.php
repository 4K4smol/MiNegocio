<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistroVerificacionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_registro_crea_solicitud_pendiente(): void
    {
        Storage::fake('verificaciones');

        $response = $this->postJson('/api/v1/register', [
            'usuario' => [
                'nombre' => 'Ana',
                'apellido1' => 'Lopez',
                'apellido2' => 'Garcia',
                'telefono' => '600000000',
                'email' => 'ana-test@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'tipo_documento_identidad_id' => 1,
                'numero_documento' => '12345678A',
            ],
            'empresa' => [
                'tipo_empresa_id' => 1,
                'nombre_fiscal' => 'Ana Test SL',
                'nombre_comercial' => 'Ana Test',
                'nif' => 'T12345678',
                'correo' => 'empresa-test@example.com',
                'telefono' => '600000001',
                'direccion_fiscal' => 'Calle Test 1',
                'codigo_postal' => '39300',
                'municipio' => 'Torrelavega',
                'provincia' => 'Cantabria',
                'pais' => 'España',
            ],
            'documentacion' => [
                'dni_frontal' => UploadedFile::fake()->create('dni-frontal.pdf', 100, 'application/pdf'),
                'dni_reverso' => UploadedFile::fake()->create('dni-reverso.pdf', 100, 'application/pdf'),
                'selfie' => UploadedFile::fake()->image('selfie.jpg'),
                'documento_fiscal' => UploadedFile::fake()->create('documento-fiscal.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertCreated();

        $userId = $response->json('data.usuario.id');

        $this->assertNotNull($userId);

        $this->assertDatabaseHas('solicitudes_verificacion', [
            'user_id' => $userId,
        ]);

        $this->assertDatabaseHas('empresas', [
            'nif' => 'T12345678',
            'activa' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ana-test@example.com',
            'activo' => false,
        ]);
    }
}
