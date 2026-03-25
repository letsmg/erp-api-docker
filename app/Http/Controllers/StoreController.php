<?php

namespace App\Http\Controllers;

use App\Services\StoreService;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    protected $service;

    public function __construct(StoreService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'min_price', 'max_price', 'brand']);
        $data = $this->service->getDataForIndex($filters);

        return Inertia::render('Store/Index', $data);
    }

    public function show($id)
    {
        $product = Product::with(['images', 'seo'])->where('is_active', true)->findOrFail($id);
        
        $relatedProducts = Product::with('images')
            ->where('brand', $product->brand)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)->get();

        return Inertia::render('Store/Show', compact('product', 'relatedProducts'));
    }

    public function acceptTerms(Request $request)
    {
        $this->service->recordTermAcceptance($request);
        return back()->with('success', 'Termos aceitos.');
    }
}