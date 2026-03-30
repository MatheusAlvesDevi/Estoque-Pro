<?php

namespace App\Services;

use App\Models\User;
use App\Repository\ExitProductRepository;
use App\Repository\ProductRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExitProductService
{
    protected $repository;
    protected $exitRepository;
    protected $productRepository;

    public function __construct(
        ExitProductRepository $exitRepository,
        ProductRepository $productRepository
    )
    {
        $this->repository = $exitRepository;
        $this->exitRepository = $exitRepository;
        $this->productRepository = $productRepository;
    }

    public function createExit(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = $this->productRepository->find($data['product_id']);
            $quantity = (int) $data['quantity'];

            if ($product->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Estoque insuficiente para registrar a saida.'
                ]);
            }

            $payload = $data;
            $payload['user_id'] = $data['user_id'] ?? Auth::id() ?? User::query()->value('id');

            if (!$payload['user_id']) {
                throw ValidationException::withMessages([
                    'user_id' => 'Nenhum usuario disponivel para registrar a saida.'
                ]);
            }

            $exit = $this->exitRepository->create($payload);

            $product->quantity -= $quantity;
            $product->save();

            return $exit;
        });
    }


     public function getAll()
    {
        return $this->repository->all();
    }

    public function getById($id)
    {
        return $this->repository->find($id);
    }
}

