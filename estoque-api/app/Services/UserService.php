<?php

namespace App\Services;

use App\Repository\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getById($id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }

    public function update($id,array $data)
    {
        if(isset($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }

        return $this->repository->update($id,$data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}