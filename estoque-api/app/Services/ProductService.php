<?php

namespace App\Services;

use App\Models\EntryProduct;
use App\Models\ExitProduct;
use App\Models\User;
use App\Repository\ProductRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductService
{
    protected $repository;
    protected $entryProductService;

    public function __construct(ProductRepository $repository, EntryProductService $entryProductService)
    {
        $this->repository = $repository;
        $this->entryProductService = $entryProductService;
    }

    /**
     * Retorna a listagem consolidada de produtos.
     */
    public function getAllProducts()
    {
        return $this->repository->all();
    }

    /**
     * Recupera um produto por identificador.
     */
    public function getProductById($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Cria o produto e registra a entrada inicial em uma única transação.
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $initialQuantity = (int) $data['quantity'];

            // O saldo inicial é controlado exclusivamente pelo fluxo de entrada.
            $productData = $data;
            $productData['quantity'] = 0;

            $product = $this->repository->create($productData);

            $userId = Auth::id() ?? User::query()->value('id');

            if (!$userId) {
                throw new RuntimeException('Nenhum usuario encontrado para registrar a entrada inicial.');
            }

            $this->entryProductService->create([
                'name' => 'Entrada inicial - ' . $product->name,
                'data_de_entrada' => now()->toDateString(),
                'quantity' => $initialQuantity,
                'reason' => 'Cadastro inicial do produto',
                'product_id' => $product->id,
                'user_id' => $userId,
            ]);

            return $this->repository->find($product->id);
        });
    }

    /**
     * Atualiza os dados de produto com base no identificador.
     */
    public function updateProduct($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Remove produto e registros relacionados de movimentação de forma atômica.
     */
    public function deleteProduct($id)
    {
        return DB::transaction(function () use ($id) {
            ExitProduct::query()->where('product_id', $id)->delete();
            EntryProduct::query()->where('product_id', $id)->delete();

            return $this->repository->delete($id);
        });
    }

}
