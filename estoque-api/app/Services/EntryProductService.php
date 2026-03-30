<?php

namespace App\Services;

use App\Models\User;
use App\Repository\EntryProductRepository;
use App\Repository\ProductRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EntryProductService
{
    protected $repository;
    protected $productRepository;

    public function __construct(
        EntryProductRepository $repository,
        ProductRepository $productRepository
    ){
        $this->repository = $repository;
        $this->productRepository = $productRepository;
    }

    public function create(array $data)
    {
        $payload = $data;
        $payload['user_id'] = $data['user_id'] ?? Auth::id() ?? User::query()->value('id');

        if (!$payload['user_id']) {
            throw ValidationException::withMessages([
                'user_id' => 'Nenhum usuario disponivel para registrar a entrada.'
            ]);
        }

        $entry = $this->repository->create($payload);

        $product = $this->productRepository->find($data['product_id']);

        $product->quantity += $data['quantity'];
        $product->save();

        return $entry;
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