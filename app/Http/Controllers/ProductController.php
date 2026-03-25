<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\Product\StoreProductRequest;
use App\Services\ProductService;
use App\Repositories\ProductRepository;
use Inertia\Inertia;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(ProductService $service, ProductRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => $this->repository->getAll(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'suppliers' => $this->repository->getActiveSuppliers()
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->service->storeProduct($request->validated(), $request);
        return redirect()->route('products.index')->with('message', 'Produto cadastrado!');
    }

    public function edit(Product $product) 
    {
        $product->load(['seo', 'images']);
        
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'suppliers' => Supplier::all()
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // O Service agora cuida de salvar os dados, SEO e a ordem das imagens
        $this->service->updateProduct($product, $request->all(), $request);

        return redirect()->route('products.index')
            ->with('message', 'Produto atualizado com sucesso!');
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('message', 'Status atualizado!');
    }

    public function destroy(Product $product)
    {
        $this->service->deleteProduct($product);
        return redirect()->route('products.index')->with('message', 'Removido com sucesso.');
    }

    public function show(Product $product)
    {
        $product->load(['supplier', 'images']);

        return Inertia::render('Products/Show', [
            'product' => $product
        ]);
    }
}