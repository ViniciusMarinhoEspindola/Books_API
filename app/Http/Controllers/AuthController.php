<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Http\Requests\AuthRequest;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password))
            return response()->json(['message' => 'Credenciais inválidas'], 401);

        return response()->json([
            'access_token' => $usuario->createToken('access-token')->plainTextToken,
            'token_type'   => 'Bearer',
        ]);
    }
}