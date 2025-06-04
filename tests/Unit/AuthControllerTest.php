<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AuthController;
use App\Models\Usuario;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_login_returns_token(): void
    {
        $usuario = Usuario::factory()->create([
            'nome' => 'Usuario Teste',
            'email' => 'teste@email.com',
            'password' => Hash::make('senha123'),
        ]);

        $request = AuthRequest::create('/api/v1/auth/token', 'POST', [
            'email' => 'teste@email.com',
            'password' => 'senha123',
        ]);

        $controller = new AuthController();

        $response = $controller->login($request);
        $responseData = $response->getData(true);

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertArrayHasKey('token_type', $responseData);
        $this->assertEquals('Bearer', $responseData['token_type']);
    }
}
