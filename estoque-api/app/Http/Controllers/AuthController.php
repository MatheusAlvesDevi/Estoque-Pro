<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
    * Autentica o usuário via credenciais e emite um token de acesso Sanctum.
     *
    * Valida o payload de entrada, verifica a senha com hash seguro e retorna
    * os dados essenciais do usuário junto ao plain text token para uso no cliente.
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'As credenciais fornecidas estão incorretas.',
            ]);
        }

        // Emite token de acesso para autenticação Bearer nas próximas requisições.
        $token = $user->createToken('Estoque Pro')->plainTextToken;

        return (new AuthResource($user, $token))->response()->setStatusCode(200);
    }

    /**
        * Encerra a sessão da API revogando o token atual do usuário autenticado.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ], 200);
    }
}
