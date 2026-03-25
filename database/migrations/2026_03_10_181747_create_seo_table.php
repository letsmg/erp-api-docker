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
            
            // Em vez de $table->morphs('seoable'), fazemos manualmente:
            $table->unsignedBigInteger('seoable_id');
            $table->string('seoable_type');
            
            // SEO Fields
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

            // ÍNDICES PARA PERFORMANCE
            $table->index(['seoable_id', 'seoable_type']);
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
