<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Usuario;
use App\Models\Indice;

class Livro extends Model
{
    /** @use HasFactory<\Database\Factories\LivroFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'usuario_publicador_id',
        'titulo',
    ];

    public function publicador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_publicador_id');
    }

    public function indices(): HasMany
    {
        return $this->hasMany(Indice::class, 'livro_id');
    }
}
