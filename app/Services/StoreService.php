<?php

namespace App\Services;

use App\Repositories\StoreRepository;
use App\Models\TermAcceptance;
use Illuminate\Http\Request;

class StoreService
{
    protected $repository;

    public function __construct(StoreRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDataForIndex(array $filters)
    {
        return [
            'products' => $this->repository->getFilteredProducts($filters),
            'featuredProducts' => $this->repository->getFeaturedProducts(),
            'onSaleProducts' => $this->repository->getOnSaleProducts(),
            'brands' => $this->repository->getAllBrands(),
            'filters' => $filters,
        ];
    }

    public function recordTermAcceptance(Request $request)
    {
        return TermAcceptance::create([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => now(),
            'term_version' => '1.0'
        ]);
    }
}