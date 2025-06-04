<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo('Usuario', 'usuario_publicador_id');
    }
}
