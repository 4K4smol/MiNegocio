<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="ApiSuccessResponse",
     *     type="object",
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(property="message", type="string", example="Operación exitosa"),
     *     @OA\Property(property="data", type="object", nullable=true),
     *     @OA\Property(property="meta", type="object")
     * )
     *
     * Genera una respuesta JSON exitosa con una estructura estandarizada.
     *
     * @param array<string, mixed>|list<mixed>|null $data
     * @param string $message
     * @param int $statusCode
     */
    protected function success(
        array|null $data = [],
        string $message = 'Operación exitosa.',
        int $statusCode = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * @OA\Schema(
     *     schema="ApiErrorResponse",
     *     type="object",
     *     @OA\Property(property="success", type="boolean", example=false),
     *     @OA\Property(property="message", type="string", example="Ocurrió un error"),
     *     @OA\Property(property="errors", type="object", nullable=true)
     * )
     *
     * Genera una respuesta JSON de error con una estructura estandarizada.
     *
     * @param string $message
     * @param int $statusCode
     * @param array<string, mixed>|list<mixed>|null $errors
     */
    protected function error(
        string $message,
        int $statusCode = 400,
        array|null $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?? (object) [],
        ], $statusCode);
    }

    /**
     * @OA\Response(
     *     response=201,
     *     description="Recurso creado exitosamente"
     * )
     *
     * Devuelve una respuesta para creación de recursos (HTTP 201).
     *
     * @param array<string, mixed>|list<mixed>|null $data
     * @param string $message
     */
    protected function created(
        array|null $data = [],
        string $message = 'Recurso creado correctamente.',
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Recurso actualizado exitosamente"
     * )
     *
     * Devuelve una respuesta para actualización de recursos (HTTP 200).
     *
     * @param array<string, mixed>|list<mixed>|null $data
     * @param string $message
     */
    protected function updated(
        array|null $data = [],
        string $message = 'Recurso actualizado correctamente.',
    ): JsonResponse {
        return $this->success($data, $message, 200);
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Recurso eliminado exitosamente"
     * )
     *
     * Devuelve una respuesta para eliminación lógica o física (HTTP 200).
     */
    protected function deleted(
        string $message = 'Recurso eliminado correctamente.'
    ): JsonResponse {
        return $this->success(null, $message, 200);
    }

    /**
     * @OA\Response(
     *     response=404,
     *     description="Recurso no encontrado"
     * )
     *
     * Devuelve una respuesta cuando un recurso no existe (HTTP 404).
     */
    protected function notFound(
        string $message = 'Recurso no encontrado.',
        array|null $errors = null
    ): JsonResponse {
        return $this->error($message, 404, $errors);
    }

    /**
     * @OA\Response(
     *     response=403,
     *     description="Acceso prohibido"
     * )
     *
     * Devuelve una respuesta cuando no hay permisos suficientes (HTTP 403).
     */
    protected function forbidden(
        string $message = 'No tienes permisos para realizar esta acción.',
        array|null $errors = null
    ): JsonResponse {
        return $this->error($message, 403, $errors);
    }

    /**
     * @OA\Response(
     *     response=422,
     *     description="Error de validación"
     * )
     *
     * Devuelve una respuesta para errores de validación (HTTP 422).
     *
     * @param array<string, mixed>|list<mixed> $errors
     */
    protected function validationError(
        array $errors,
        string $message = 'Error de validación.'
    ): JsonResponse {
        return $this->error($message, 422, $errors);
    }
}
