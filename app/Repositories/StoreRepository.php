<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StoreRepository
{
    public function getFilteredProducts(array $filters)
    {
        $query = Product::query()
            ->with(['images', 'seo'])
            ->where('is_active', true);

        if (!empty($filters['search']) && strlen($filters['search']) >= 3) {
            // Usando unaccent para ignorar acentuação no PostgreSQL
            $searchTerm = '%' . $filters['search'] . '%';
            $query->whereRaw("unaccent(description) ilike unaccent(?)", [$searchTerm]);
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(9);
    }

    public function getFeaturedProducts(int $limit = 5)
    {
        return Product::with(['images', 'seo'])
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function getOnSaleProducts(int $limit = 8)
    {
        return Product::with(['images'])
            ->where('is_active', true)
            ->orderBy('sale_price', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getAllBrands()
    {
        return Product::distinct()->whereNotNull('brand')->pluck('brand');
    }
}