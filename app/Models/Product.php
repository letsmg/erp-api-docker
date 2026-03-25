<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
        
    protected $fillable = [
        'supplier_id', 'description', 'brand', 'model', 'size', 
        'collection', 'gender', 'cost_price', 'sale_price', 
        'promo_price', 'promo_start_at', 'promo_end_at',
        'barcode', 'stock_quantity', 'is_active', 'is_featured',
        'slug',
        // Novos campos de frete
        'weight', 'width', 'height', 'length', 'free_shipping'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'free_shipping' => 'boolean', 
        'promo_start_at' => 'datetime',
        'promo_end_at' => 'datetime',
        'cost_price' => 'decimal:2', // Garantindo precisão numérica
        'sale_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'length' => 'decimal:2',
    ];

    protected $appends = ['current_price', 'seo_display'];

    /**
     * Ciclo de Vida do Model (Eventos)
     */
    protected static function booted()
    {
        // Ao criar um produto
        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = Str::slug($product->description) . '-' . Str::random(5);
            }
        });

        // Ao deletar um produto
        static::deleting(function ($product) {
            // Deleta o SEO polimórfico se ele existir
            if ($product->seo) {
                $product->seo()->delete();
            }

            // Opcional: Se quiser que as imagens sumam do BANCO no delete
            // (O cascade na migration já faz isso, mas aqui reforça)
            $product->images()->delete();
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): HasMany
    {
        // Ordenação por ID garante que a primeira foto enviada seja a principal no front
        return $this->hasMany(ProductImage::class)->orderBy('order', 'asc')->orderBy('id', 'asc');
    }
    
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
     * Accessors (Campos Virtuais)
     */
    public function getCurrentPriceAttribute()
    {
        $now = now();
        if ($this->promo_price && $this->promo_start_at && $this->promo_end_at) {
            if ($now->between($this->promo_start_at, $this->promo_end_at)) {
                return (float) $this->promo_price;
            }
        }
        return (float) $this->sale_price;
    }

    public function getSeoDisplayAttribute()
    {
        // Carrega o relacionamento caso ele não esteja presente
        $seo = $this->seo;

        return [
            'meta_title'       => $seo?->meta_title ?: $this->h1,
            'meta_description' => $seo?->meta_description ?: "Confira {$this->description} com o melhor preço na nossa loja.",
            'slug'             => $seo?->slug ?: $this->slug,
            'h1'               => $seo?->h1 ?: $this->title,
            'meta_keywords'    => $seo?->meta_keywords ?: str_replace(' ', ', ', $this->description),
        ];
    }
}