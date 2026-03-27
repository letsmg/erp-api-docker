<?php

namespace App\Modules\Product\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use MongoDB\Laravel\Relations\MorphOne;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id', 'category_id', 'description', 'brand', 'model', 'size', 'collection', 'gender',
        'cost_price', 'sale_price', 'promo_price', 'promo_start_at', 'promo_end_at', 'barcode',
        'stock_quantity', 'is_active', 'is_featured', 'slug', 'weight', 'width', 'height', 'length', 'free_shipping',
    ];

    protected $casts = [
        'is_active' => 'boolean', 'is_featured' => 'boolean', 'free_shipping' => 'boolean',
        'promo_start_at' => 'datetime', 'promo_end_at' => 'datetime', 'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2', 'promo_price' => 'decimal:2', 'weight' => 'decimal:3',
        'width' => 'decimal:2', 'height' => 'decimal:2', 'length' => 'decimal:2',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];

    protected $appends = ['current_price', 'seo_display'];

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function ($product) { 
            if (! $product->slug) { 
                $product->slug = self::generateUniqueSlug($product->description); 
            }
            if (empty($product->_id)) {
                $product->_id = (string) new \MongoDB\BSON\ObjectId();
            }
        });
        static::updating(function ($product) { 
            if ($product->isDirty('description')) { 
                $product->slug = self::generateUniqueSlug($product->description); 
            } 
        });
        static::deleting(function ($product) { 
            if ($product->seo) { $product->seo()->delete(); } 
            $product->images()->delete(); 
        });
    }

    public static function generateUniqueSlug($text): string
    {
        $baseSlug = Str::slug($text); 
        $slug = $baseSlug; 
        $count = 1;
        while (self::where('slug', $slug)->exists()) { 
            $slug = $baseSlug.'-'.$count++; 
        }
        return $slug;
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('order', 'asc')->orderBy('id', 'asc'); }
    public function seo(): MorphOne { return $this->morphOne(Seo::class, 'seoable'); }

    public function getCurrentPriceAttribute(): float
    {
        $now = now();
        if ($this->promo_price && $this->promo_start_at && $this->promo_end_at && $now->between($this->promo_start_at, $this->promo_end_at)) {
            return (float) $this->promo_price;
        }
        return (float) $this->sale_price;
    }

    public function getSeoDisplayAttribute(): array
    {
        $seo = $this->seo;
        return [
            'meta_title' => $seo?->meta_title ?: $this->description,
            'meta_description' => $seo?->meta_description ?: "Confira {$this->description} com o melhor preco na nossa loja.",
            'h1' => $seo?->h1 ?: $this->description,
            'meta_keywords' => $seo?->meta_keywords ?: str_replace(' ', ', ', $this->description),
            'slug' => $this->slug,
            'canonical_url' => config('app.url').'/api/v1/catalog/products/'.$this->slug,
        ];
    }

    // MongoDB search helper for short queries
    public function scopeShortSearch($query, $term)
    {
        if (strlen($term) < 4) {
            return $query->where(function ($q) use ($term) {
                $q->where('barcode', $term)
                  ->orWhere('model', 'regex', new \MongoDB\BSON\Regex("^{$term}", 'i'))
                  ->orWhere('brand', 'regex', new \MongoDB\BSON\Regex("^{$term}", 'i'));
            });
        }
        
        return $query->where('description', 'regex', new \MongoDB\BSON\Regex($term, 'i'));
    }
}
