<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Supplier;

class ProductRepository
{
    /**
     * Busca produtos com filtros de pesquisa e paginação.
     */
    public function getFiltered(array $filters)
    {
        $query = Product::query()
            ->with(['supplier:id,company_name', 'images' => function($q) {
                $q->orderBy('order', 'asc');
            }]);

        // 🔒 1. Filtro de Bloqueados (is_active)
        // Se o filtro 'blocked' vier como 1, filtramos apenas os inativos (false)
        if (isset($filters['blocked']) && $filters['blocked'] == 1) {
            $query->where('is_active', false);
        }

        // 🔍 2. Filtro de Busca Textual
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                // Grupo de Busca por Texto
                $q->where(function ($sub) use ($search) {
                    $searchTerm = "%{$search}%";
                    $sub->whereRaw("unaccent(description) ilike unaccent(?)", [$searchTerm])
                        ->orWhereRaw("unaccent(brand) ilike unaccent(?)", [$searchTerm])
                        ->orWhereRaw("unaccent(model) ilike unaccent(?)", [$searchTerm]);
                });

                // Grupo de Busca por Preço
                $numericValue = $this->parseNumeric($search);
                if ($numericValue > 0) {
                    $q->orWhere('sale_price', '<=', $numericValue)
                    ->orWhere('promo_price', '<=', $numericValue);
                }
            });

            $query->orderByRaw('COALESCE(promo_price, sale_price) DESC');
        } else {
            $query->latest();
        }

        // Importante: usamos withQueryString para manter os filtros ao trocar de página
        return $query->paginate(12)->withQueryString();
    }

    /**
     * Retorna fornecedores ativos para o formulário de cadastro.
     */
    public function getActiveSuppliers() 
    {
        return Supplier::select('id', 'company_name')
            ->orderBy('company_name')
            ->get();
    }

    /**
     * Cria um produto aplicando a regra de ativação por nível de usuário.
     */
    public function create(array $data) 
    { 
        $user = auth()->user();

        // 1. Definimos o status de ativação
        $data['is_active'] = ($user && $user->access_level == 1);

        // 2. Criamos uma lista com os campos que pertencem à tabela 'seos'
        $seoFields = ['meta_title', 'meta_description', 'meta_keywords', 'h1', 'text1'];
        
        // 3. O SEGREDO: Criamos um array excluindo os campos de SEO
        // Isso evita que o SQL tente inserir 'meta_title' na tabela 'products'
        $productData = collect($data)->except($seoFields)->toArray();

        // 4. Salvamos o Produto com os dados limpos
        $product = Product::create($productData);

        // 5. Agora pegamos apenas os dados de SEO para salvar na tabela correta
        $seoData = collect($data)->only($seoFields)->filter()->toArray();

        if (!empty($seoData)) {
            // O Laravel usa a relação MorphOne para criar o registro em 'seos'
            $product->seo()->create($seoData);
        }

        return $product; 
    }
    /**
     * Atualiza os dados básicos do produto.
     */
    public function update(Product $product, array $data) 
    { 
        // 1. Campos que permitimos atualizar via request
        $productFields = [
            'description', 'supplier_id', 'barcode', 'brand', 'model', 
            'collection', 'size', 'gender', 'stock_quantity', 'slug',
            'cost_price', 'sale_price', 'promo_price', 'promo_start_at', 
            'promo_end_at', 'weight', 'width', 'height', 'length', 'free_shipping',
            'is_active' 
        ];

        $filteredData = collect($data)->only($productFields)->toArray();

        // 2. Trava de Segurança
        $user = auth()->user();
        if ($user && $user->access_level !== 1) {
            // Se não for admin, removemos o is_active do que será salvo
            unset($filteredData['is_active']);
        }

        // 3. Atualização
        // Se filteredData tiver 'description', ela TEM que ser salva aqui
        $product->update($filteredData); 
        
        //dump($product->getChanges());

        return $product; 
    }

    /**
     * Alterna o status de destaque do produto. ⭐
     */
    public function toggleFeatured(Product $product)
    {
        $product->update([
            'is_featured' => !$product->is_featured
        ]);
        
        return $product;
    }

    /**
     * Remove um produto do banco de dados.
     */
    public function delete(Product $product) 
    { 
        return $product->delete(); 
    }

    /**
     * Limpa strings para busca numérica (preços).
     */
    private function parseNumeric($value) 
    {
        $cleaned = preg_replace('/[^0-9,.]/', '', str_replace(',', '.', $value));
        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }
}