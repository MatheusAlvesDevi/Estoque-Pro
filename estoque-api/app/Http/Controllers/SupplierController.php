<?php
namespace App\Http\Controllers;

use App\Services\SupplierService;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;

class SupplierController extends Controller
{
    protected $service;

    public function __construct(SupplierService $service)
    {
        $this->service = $service;
    }

    // listar fornecedores
    public function index()
    {
        $suppliers = $this->service->getAll();

        return SupplierResource::collection($suppliers);
    }

    // buscar fornecedor por id
    public function show($id)
    {
        $supplier = $this->service->getById($id);

        return new SupplierResource($supplier);
    }

    // criar fornecedor
    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->service->create($request->validated());

        return new SupplierResource($supplier);
    }

    // atualizar fornecedor
    public function update(UpdateSupplierRequest $request, $id)
    {
        $supplier = $this->service->update($id, $request->validated());

        return new SupplierResource($supplier);
    }

    // deletar fornecedor
    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Fornecedor removido com sucesso'
        ]);
    }
}