<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerbForm extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'content', 'word_id'];

    public function synonymWord()
    {
        return $this->belongsTo(SynonymWord::class, 'word_id', 'id');
    }
}
