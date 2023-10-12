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
        Schema::create('synonym_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('word_id')->unsigned();
            $table->bigInteger('group_id')->unsigned();
            $table->timestamps();

            $table->foreign('word_id')->references('id')->on('synonym_words');
            $table->foreign('group_id')->references('id')->on('synonym_words');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('synonym_groups');
    }
};
