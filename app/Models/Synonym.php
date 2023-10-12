<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Synonym extends Model
{
    use HasFactory;

    public function word()
    {
        return $this->belongsTo(SynonymWord::class, 'word_id', 'id');
    }
}
