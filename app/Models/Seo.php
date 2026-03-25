<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    protected $table = 'seo';

    protected $fillable = [
        'slug', 'meta_title', 'meta_description', 'meta_keywords', 
        'canonical_url', 'h1', 'text1', 'h2', 'text2', 
        'schema_markup', 'google_tag_manager', 
        'seoable_id', 'seoable_type'
    ];

    /**
     * IMPORTANTE: Faz o Laravel entender meta_keywords como Array 
     * em vez de uma string pura.
     */
    protected $casts = [
        'meta_keywords' => 'array',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->h1;
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: 'Confira os detalhes deste produto em nossa loja oficial.';
    }
}