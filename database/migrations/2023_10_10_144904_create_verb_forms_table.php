<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('verb_forms', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('word_id')->unsigned();
            $table->string('type');
            $table->string('title');
            $table->text('content');
            $table->timestamps();

            $table->foreign('word_id')->references('id')->on('synonym_words');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verb_forms');
    }
};
