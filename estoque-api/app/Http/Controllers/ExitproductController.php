<?php 
    
namespace App\Http\Controllers;

use App\Services\ExitProductService;
use App\Http\Requests\StoreExitProductRequest;
use App\Http\Resources\ExitProductResource;

class ExitProductController extends Controller
{
    protected $service;

    public function __construct(ExitProductService $service)
    {
        $this->service = $service;
    }

    // listar saídas
    public function index()
    {
        $exits = $this->service->getAll();

        return ExitProductResource::collection($exits);
    }

    // registrar saída
    public function store(StoreExitProductRequest $request)
    {
        $exit = $this->service->createExit($request->validated());

        return new ExitProductResource($exit);
    }

    // buscar saída por id
    public function show($id)
    {
        $exit = $this->service->getById($id);

        return new ExitProductResource($exit);
    }
}