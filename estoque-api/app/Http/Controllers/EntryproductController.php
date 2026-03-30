<?php
namespace App\Http\Controllers;

use App\Services\EntryProductService;
use App\Http\Requests\StoreEntryProductRequest;
use App\Http\Resources\EntryProductResource;

class EntryProductController extends Controller
{
    protected $service;

    public function __construct(EntryProductService $service)
    {
        $this->service = $service;
    }

    // listar entradas
    public function index()
    {
        $entries = $this->service->getAll();

        return EntryProductResource::collection($entries);
    }

    // registrar entrada
    public function store(StoreEntryProductRequest $request)
    {
        $entry = $this->service->create($request->validated());

        return new EntryProductResource($entry);
    }

    // buscar entrada por id
    public function show($id)
    {
        $entry = $this->service->getById($id);

        return new EntryProductResource($entry);
    }
}