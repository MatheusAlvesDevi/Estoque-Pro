<?php


namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    /**
     * Retorna a coleção paginável/serializada de produtos disponíveis.
     */
    public function index()
    {
        $products = $this->service->getAllProducts();

        return ProductResource::collection($products);
    }

    /**
     * Retorna os detalhes de um produto específico pelo identificador.
     */
    public function show($id)
    {
        $product = $this->service->getProductById($id);

        return new ProductResource($product);
    }

    /**
     * Cria um novo produto a partir de dados previamente validados.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $product = $this->service->create($data);
       
        return new ProductResource($product);
    }

    /**
     * Atualiza parcialmente um produto existente com base no identificador.
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = $this->service->updateProduct($id, $request->validated());

        return new ProductResource($product);
    }

    /**
     * Remove o produto e seus vínculos operacionais conforme regra de negócio.
     */
    public function destroy($id)
    {
        $this->service->deleteProduct($id);

        return response()->json([
            'message' => 'Produto deletado com sucesso'
        ]);
    }
}