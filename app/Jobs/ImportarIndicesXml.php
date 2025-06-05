<?php

namespace App\Jobs;

use App\Models\Livro;
use App\Models\Indice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportarIndicesXml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $livroId;
    public string $xmlContent;

    public function __construct(int $livroId, string $xmlContent)
    {
        $this->livroId = $livroId;
        $this->xmlContent = $xmlContent;
    }

    public function handle(): void
    {
        $xml = simplexml_load_string($this->xmlContent);

        if (!$xml) {
            throw new \Exception('Erro ao processar XML.');
        }

        $this->importarItens($xml, null);
    }

    public function importarItens($xmlNode, $indicePaiId = null)
    {
        foreach ($xmlNode->item as $item) {
            $titulo = (string) $item['titulo'];
            $pagina = (int) $item['pagina'];

            $indice = Indice::create([
                'livro_id' => $this->livroId,
                'indice_pai_id' => $indicePaiId,
                'titulo' => $titulo,
                'pagina' => $pagina,
            ]);

            if ($item->item) {
                $this->importarItens($item, $indice->id);
            }
        }
    }
}
