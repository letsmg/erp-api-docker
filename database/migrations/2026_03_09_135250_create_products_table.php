<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('description', 150);
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('size', 20)->nullable();
            $table->string('collection', 100)->nullable();
            $table->string('gender', 30)->nullable();                        
            
            // Preços e Promoção
            $table->decimal('cost_price', 12, 2);
            $table->decimal('sale_price', 12, 2);
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->dateTime('promo_start_at')->nullable();
            $table->dateTime('promo_end_at')->nullable();

            $table->string('barcode', 50)->nullable()->unique();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->string('slug')->unique();            

            $table->decimal('weight', 8, 3)->nullable(); // em kg (ex: 1.500)
            $table->decimal('width', 8, 2)->nullable();  // em cm
            $table->decimal('height', 8, 2)->nullable();  // em cm
            $table->decimal('length', 8, 2)->nullable(); // em cm
            
            // Opcional: frete grátis por produto
            $table->boolean('free_shipping')->default(false);
            
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('created_at');
            $table->fullText(['description', 'brand', 'model']);
        });

        
    }
    public function down(): void { Schema::dropIfExists('products'); }
};