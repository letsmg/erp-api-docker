<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    // Adicionei o nome da tabela caso o Laravel não a encontre automaticamente como 'seo'
    protected $table = 'seo';

    protected $fillable = [
        'slug', 'meta_title', 'meta_description', 'meta_keywords', 
        'canonical_url', 'h1', 'text1', 'h2', 'text2', 
        'schema_markup', 'google_tag_manager', 'ads', 
        'seoable_id', 'seoable_type'
    ];

    /**
     * Relacionamento Polimórfico
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 💡 ACESSOR DE TÍTULO (Opcional, mas recomendado)
     * Se o meta_title estiver vazio no banco, ele tenta retornar o H1.
     * Isso ajuda a evitar o erro de 'undefined' no seu Index.vue.
     */
    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->h1;
    }

    /**
     * 💡 ACESSOR DE DESCRIÇÃO
     * Retorna um texto padrão se não houver meta_description.
     */
    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: 'Confira os detalhes deste produto em nossa loja oficial.';
    }
}