<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\LivroController;
use App\Models\Livro;
use App\Models\Usuario;
use App\Http\Requests\LivroStoreRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ImportarIndicesXml;

class LivroControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_list_books(): void
    {
        $mockLivros = Livro::all()->toArray();

        $request = Request::create('/api/v1/livros', 'GET', []);

        $controller = new LivroController();
        $response = $controller->index($request);

        $this->assertEquals($mockLivros, $response->getData(true));
    }

    public function test_store_create_book()
    {
        $dados = [
            'titulo' => 'Livro Teste',
            'indices' => [
                [
                    'titulo' => 'Indice 1',
                    'pagina' => 1,
                    'subindices' => [
                        [
                            'titulo' => 'Subindice 1.1',
                            'pagina' => 2,
                            'subindices' => []
                        ]
                    ]
                ]
            ]
        ];

        $usuario = Usuario::factory()->create([
            'nome' => 'Usuario Teste',
            'email' => 'teste@email.com',
            'password' => Hash::make('senha123'),
        ]);

        $request = LivroStoreRequest::create('/api/v1/livros', 'POST', $dados);
        $request->setUserResolver(function () use ($usuario) {
            return $usuario;
        });

        $controller = new \App\Http\Controllers\LivroController();
        $response = $controller->store($request);

        $this->assertTrue($response->getContent() != null);
    }

    public function test_import_indices()
    {
        $xmlContent = '<indice><item pagina="1" titulo="Capítulo 1"></item></indice>';
        $file = UploadedFile::fake()->createWithContent('indices.xml', $xmlContent);

        $request = Request::create('/api/v1/livros/1/importar-indices-xml', 'POST', [], [], [
            'xml' => $file
        ]);

        $usuario = Usuario::factory()->create([
            'nome' => 'Usuario Teste',
            'email' => 'teste@email.com',
            'password' => Hash::make('senha123'),
        ]);

        $livro = Livro::create([
            'titulo' => 'Livro de Teste',
            'usuario_publicador_id' => $usuario->id,
        ]);

        Bus::fake();

        $controller = new LivroController();
        $response = $controller->import($request, $livro->id);

        Bus::assertDispatched(ImportarIndicesXml::class, function ($job) use ($xmlContent) {
            return $job->livroId === 1 && $job->xmlContent === $xmlContent;
        });

        $this->assertEquals(202, $response->status());
    }
}
