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

        // 🔍 Filtro de Busca
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                // 1. Grupo de Busca por Texto (Acentos e Case Insensitive)
                $q->where(function ($sub) use ($search) {
                    $searchTerm = "%{$search}%";
                    $sub->whereRaw("unaccent(description) ilike unaccent(?)", [$searchTerm])
                        ->orWhereRaw("unaccent(brand) ilike unaccent(?)", [$searchTerm])
                        ->orWhereRaw("unaccent(model) ilike unaccent(?)", [$searchTerm]);
                });

                // 2. Grupo de Busca por Preço (Se for numérico)
                $numericValue = $this->parseNumeric($search);
                if ($numericValue > 0) {
                    $q->orWhere('sale_price', '<=', $numericValue)
                    ->orWhere('promo_price', '<=', $numericValue);
                }
            });

            // 📈 Ordenação por preço (considerando promoções)
            $query->orderByRaw('COALESCE(promo_price, sale_price) DESC');
        } else {
            $query->latest();
        }

        return $query->paginate(12);
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

        // 🛡️ Regra de Negócio: Admin (1) já cadastra como Ativo.
        $data['is_active'] = ($user && $user->access_level == 1);

        return Product::create($data); 
    }

    /**
     * Atualiza os dados básicos do produto.
     */
    public function update(Product $product, array $data) 
    { 
        $product->update($data); 
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