<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Livro;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario::factory(10)->create();

        $usuario = Usuario::factory()->create([
            'nome' => 'Usuario Teste',
            'email' => 'user@example.com',
            'password' => \Hash::make('senha123'),
        ]);

        $livro = Livro::create([
            'usuario_publicador_id' => $usuario->id,
            'titulo' => 'Livro de Teste',
        ]);
    }
}
