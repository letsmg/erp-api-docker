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
        Schema::create('seo', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable'); // Cria seoable_id e seoable_type
            //conceito de poliformismo.. gera dois campos seoable_type e seoable_id
            //caso algum dos outros campos fique em branco, pelo type ele sabe quais
            //as meta tags usar e fica melhor que criar apenas uma lista de booleanos
            //1 para seo de produtos, 2 para categorais, etc...
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('h1')->nullable();       
            $table->text('text1')->nullable();      
            $table->string('h2')->nullable();
            $table->text('text2')->nullable();
            $table->text('schema_markup')->nullable();
            $table->text('google_tag_manager')->nullable();
            $table->text('ads')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo');
    }
};
