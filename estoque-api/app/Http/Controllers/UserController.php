<?php 

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Lista os usuários disponíveis com serialização padronizada de resposta.
     */
    public function index()
    {
        $users = $this->service->getAll();

        return UserResource::collection($users);
    }

    /**
     * Obtém um usuário específico pelo identificador informado.
     */
    public function show($id)
    {
        $user = $this->service->getById($id);

        return new UserResource($user);
    }

    /**
     * Cria um novo usuário com base no payload validado pela camada de Request.
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->service->create($request->validated());

        return new UserResource($user);
    }

    /**
     * Atualiza os dados de um usuário existente conforme regras de validação.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->service->update($id, $request->validated());

        return new UserResource($user);
    }

    /**
     * Remove o usuário do sistema e retorna confirmação de sucesso.
     */
    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Usuário removido com sucesso'
        ]);
    }
}


?>