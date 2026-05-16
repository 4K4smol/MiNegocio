<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SolicitudVerificacion;
use App\Models\User;
use App\Models\VerificacionEmpresa;
use App\Models\VerificacionUsuario;
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
        Storage::fake('verificaciones');
    }

    public function test_registro_autonomo_correcto_crea_solicitud_y_verificaciones_pendientes(): void
    {
        $response = $this->postJson('/api/v1/register', $this->payloadRegistro('autonomo'));

        $response->assertCreated();

        $user = User::query()->where('email', 'registro-autonomo@example.com')->firstOrFail();
        $solicitud = SolicitudVerificacion::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertFalse($user->activo);
        $this->assertFalse($user->empresa->activa);
        $this->assertSame('pendiente', $solicitud->estado_identidad);
        $this->assertSame('pendiente', $solicitud->estado_empresa);
        $this->assertNull($solicitud->estado_representacion);

        $this->assertDatabaseHas('verificaciones_usuario', [
            'user_id' => $user->id,
            'numero_documento' => '12345678A',
        ]);
        $this->assertDatabaseHas('verificaciones_empresa', [
            'empresa_id' => $user->empresa_id,
        ]);
        $this->assertDatabaseHas('documentos_verificacion', [
            'solicitud_verificacion_id' => $solicitud->id,
            'tipo_documento' => 'dni_frontal',
        ]);
        $this->assertDatabaseHas('documentos_verificacion', [
            'solicitud_verificacion_id' => $solicitud->id,
            'tipo_documento' => 'documento_fiscal',
        ]);

        $this->assertNotNull(VerificacionUsuario::query()->where('user_id', $user->id)->value('ruta_documento_anverso'));
        $this->assertNotNull(VerificacionEmpresa::query()->where('empresa_id', $user->empresa_id)->value('ruta_documento_fiscal'));
    }

    public function test_registro_empresa_correcto_exige_y_deja_representacion_pendiente(): void
    {
        $response = $this->postJson('/api/v1/register', $this->payloadRegistro('sociedad'));

        $response->assertCreated();

        $user = User::query()->where('email', 'registro-sociedad@example.com')->firstOrFail();
        $solicitud = SolicitudVerificacion::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertFalse($user->activo);
        $this->assertFalse($user->empresa->activa);
        $this->assertSame('pendiente', $solicitud->estado_representacion);

        $this->assertDatabaseHas('documentos_verificacion', [
            'solicitud_verificacion_id' => $solicitud->id,
            'tipo_documento' => 'documento_representacion',
        ]);
        $this->assertNotNull(VerificacionEmpresa::query()->where('empresa_id', $user->empresa_id)->value('ruta_documento_representacion'));
    }

    public function test_registro_empresa_sin_documento_representacion_devuelve_422(): void
    {
        $payload = $this->payloadRegistro('sociedad');
        unset($payload['documentacion']['documento_representacion']);

        $this->postJson('/api/v1/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('documentacion.documento_representacion');
    }

    public function test_registro_sin_dni_frontal_devuelve_422(): void
    {
        $payload = $this->payloadRegistro('autonomo');
        unset($payload['documentacion']['dni_frontal']);

        $this->postJson('/api/v1/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('documentacion.dni_frontal');
    }

    public function test_registro_autonomo_sin_documento_fiscal_devuelve_422(): void
    {
        $payload = $this->payloadRegistro('autonomo');
        unset($payload['documentacion']['documento_fiscal']);

        $this->postJson('/api/v1/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('documentacion.documento_fiscal');
    }

    public function test_registro_con_dni_sin_reverso_devuelve_422(): void
    {
        $payload = $this->payloadRegistro('autonomo');
        unset($payload['documentacion']['dni_reverso']);

        $this->postJson('/api/v1/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('documentacion.dni_reverso');
    }

    private function payloadRegistro(string $tipoEmpresa): array
    {
        $tipoEmpresaId = $tipoEmpresa === 'autonomo' ? 1 : 2;
        $email = $tipoEmpresa === 'autonomo' ? 'registro-autonomo@example.com' : 'registro-sociedad@example.com';
        $nif = $tipoEmpresa === 'autonomo' ? '12345678A' : 'B92345678';

        $documentacion = [
            'dni_frontal' => UploadedFile::fake()->create('dni-frontal.pdf', 100, 'application/pdf'),
            'dni_reverso' => UploadedFile::fake()->create('dni-reverso.pdf', 100, 'application/pdf'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
            'documento_fiscal' => UploadedFile::fake()->create('documento-fiscal.pdf', 100, 'application/pdf'),
        ];

        if ($tipoEmpresa !== 'autonomo') {
            $documentacion['documento_representacion'] = UploadedFile::fake()->create('representacion.pdf', 100, 'application/pdf');
            $documentacion['registro_mercantil'] = UploadedFile::fake()->create('registro-mercantil.pdf', 100, 'application/pdf');
            $documentacion['poder_apoderamiento'] = UploadedFile::fake()->create('poder.pdf', 100, 'application/pdf');
        }

        return [
            'usuario' => [
                'nombre' => 'Ana',
                'apellido1' => 'Lopez',
                'apellido2' => 'Garcia',
                'telefono' => '600000000',
                'email' => $email,
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'tipo_documento_identidad_id' => 1,
                'numero_documento' => '12345678A',
            ],
            'empresa' => [
                'tipo_empresa_id' => $tipoEmpresaId,
                'nombre_fiscal' => $tipoEmpresa === 'autonomo' ? 'Ana Lopez' : 'Ana Test SL',
                'nombre_comercial' => 'Ana Test',
                'nif' => $nif,
                'correo' => 'empresa-'.$tipoEmpresa.'@example.com',
                'telefono' => '600000001',
                'direccion_fiscal' => 'Calle Test 1',
                'codigo_postal' => '39300',
                'municipio' => 'Torrelavega',
                'provincia' => 'Cantabria',
                'pais' => 'Espana',
            ],
            'documentacion' => $documentacion,
        ];
    }
}
