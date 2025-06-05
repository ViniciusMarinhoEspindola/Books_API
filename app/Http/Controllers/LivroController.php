<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livro;
use App\Models\Indice;
use App\Http\Requests\LivroStoreRequest;
use App\Jobs\ImportarIndicesXml;

class LivroController extends Controller
{
    public function index(Request $request)
    {
        $livros = Livro::with(['publicador', 'indices.subindices'])
                        ->when(!empty($request->titulo), function($query) use ($request) {
                            return $query->where('titulo', 'like', "%{$request->titulo}%");
                        })
                        ->when(!empty($request->titulo_do_indice), function($query) use ($request) {
                            return $query->whereHas('indices', function($subQuery) use ($request) {
                                        return $this->filtraSubindices($subQuery, $request->titulo_do_indice);
                                    })
                                    ->with('indices', function($subQuery) use ($request) {
                                        return $this->filtraSubindices($subQuery, $request->titulo_do_indice);
                                    });
                        });

        $livrosFormatados = $this->formatarLivros($livros->get(), $request->titulo_do_indice);

        return response()->json($livrosFormatados);
    }

    private function filtraSubindices($subQuery, $titulo_do_indice)
    {
        return $subQuery->where('titulo', $titulo_do_indice)
                        ->orWhereHas('subindices', function($subSubQuery) use ($titulo_do_indice) {
                            return $subSubQuery->where('titulo', $titulo_do_indice);
                        });
    }

    private function formatarLivros($livros, $titulo_do_indice = null)
    {
        $livrosFormatados = [];

        foreach ($livros as $livro) {
            $indicesFormatados = [];
            $livrosIndices = $livro->indices->where('indice_pai_id', null);

            $indicesFormatados = $livrosIndices->map(function($indice) use ($titulo_do_indice) {
                        return $this->formatarIndices($indice, $titulo_do_indice);
                    })->toArray();

            $livrosFormatados[] = [
                'titulo' => $livro->titulo,
                'usuario_publicador' => [
                    'id' => $livro->publicador->id,
                    'nome' => $livro->publicador->nome,
                ],
                'indices' => $indicesFormatados
            ];
        }

        return $livrosFormatados;
    }

    private function formatarIndices($indice, $titulo_do_indice = null)
    {
        return [
            'id' => $indice->id,
            'titulo' => $indice->titulo,
            'pagina' => $indice->pagina,
            'subindices' => (!$indice->subindices || $titulo_do_indice == $indice->titulo) ? []
                :   $indice->subindices->map(function($subindice) use ($titulo_do_indice) {
                        return $this->formatarIndices($subindice, $titulo_do_indice);
                    })
                    ->toArray()
        ];
    }

    public function store(LivroStoreRequest $request)
    {
        // Verifica se o livro já existe
        if (Livro::where('titulo', $request['titulo'])->exists())
            return response()->json(['message' => 'Livro já cadastrado'], 400);

        $livroStore = $request->toArray();
        $livroStore['usuario_publicador_id'] = $request->user()->id;

        $livro = Livro::create($livroStore);

        if (!empty($livroStore['indices'])) {
            foreach ($livroStore['indices'] as $subindice) {
                $this->salvaIndicesRecursivos($subindice, $livro->id);
            }
        }

        return response()->json($livro, 201);
    }

    private function salvaIndicesRecursivos($indice, $livro_id, $indice_pai_id = null)
    {
        $indice['livro_id'] = $livro_id;
        $indice['indice_pai_id'] = $indice_pai_id;
        $subindiceCriado = Indice::create($indice);

        if (isset($indice['subindices']) && is_array($indice['subindices'])) {
            foreach ($indice['subindices'] as $subindice) {
                $this->salvaIndicesRecursivos($subindice, $livro_id, $subindiceCriado->id);
            }
        }
    }

    public function import(Request $request, $livroId)
    {
        $xmlContent = file_get_contents($request->file('xml')->getRealPath());
        ImportarIndicesXml::dispatch($livroId, $xmlContent);

        return response()->json(['message' => 'Importação enviada para fila.'], 202);
    }
}
