<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreClienteRequest;
use App\Http\Requests\Api\V1\UpdateClienteRequest;
use App\Http\Resources\Api\V1\ClienteResource;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Cliente::class;
    }

    protected function resourceClass(): ?string
    {
        return ClienteResource::class;
    }

    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['tipoCliente'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';

                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('nombre', 'like', $search)
                        ->orWhere('apellidos', 'like', $search)
                        ->orWhere('razon_social', 'like', $search)
                        ->orWhere('dni_cif', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('telefono', 'like', $search);
                });
            });
    }

    public function store(StoreClienteRequest $request)
    {
        $data = $this->fillEmpresaIdFromUser($request->validated(), $request);

        if (empty($data['empresa_id'])) {
            return $this->validationError([
                'empresa_id' => ['No se ha podido determinar la empresa asociada al usuario.'],
            ]);
        }

        $cliente = Cliente::query()->create($data);

        return $this->created(ClienteResource::make($cliente->load(['tipoCliente']))->resolve());
    }

    public function update(UpdateClienteRequest $request, int $id)
    {
        $cliente = $this->findRecord($request, $id);

        if ($cliente === null) {
            return $this->notFound();
        }

        $data = $this->fillEmpresaIdFromUser($request->validated(), $request);

        if (empty($data['empresa_id'])) {
            $data['empresa_id'] = $cliente->empresa_id;
        }

        $cliente->fill($data);
        $cliente->save();

        return $this->updated(ClienteResource::make($cliente->load(['tipoCliente']))->resolve());
    }
}
