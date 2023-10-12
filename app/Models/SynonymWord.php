<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SynonymWord extends Model
{
    use HasFactory;

    protected $table = 'synonym_words';
    protected $fillable = ['lang', 'text', 'uppercase'];

    public function synonymGroups()
    {
        return $this->hasMany(SynonymGroup::class, 'word_id', 'id');
    }

    public function synonyms()
    {
        return $this->hasMany(Synonym::class, 'word_id', 'id');
    }

    public function verbForms()
    {
        return $this->hasMany(VerbForm::class, 'word_id', 'id');
    }
}
