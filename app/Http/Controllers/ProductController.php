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

    /**
     * Lista os produtos com suporte a busca e filtros.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        
        return Inertia::render('Products/Index', [
            'products' => $this->repository->getFiltered($filters),
            'filters' => $filters, 
        ]);
    }

    /**
     * Exibe o formulário de criação com a lista de fornecedores.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'suppliers' => $this->repository->getActiveSuppliers()
        ]);
    }

    /**
     * Salva um novo produto.
     */
    public function store(StoreProductRequest $request)
    {
        $this->service->storeProduct($request->validated(), $request);
        return redirect()->route('products.index')->with('message', 'Produto cadastrado!');
    }

    /**
     * Exibe o formulário de edição com SEO e Imagens carregados.
     */
    public function edit(Product $product) 
    {
        $product->load(['seo', 'images']);
        
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'suppliers' => Supplier::all()
        ]);
    }

    /**
     * Atualiza o produto via Service.
     */
    public function update(Request $request, Product $product)
    {
        $this->service->updateProduct($product, $request->all(), $request);

        return redirect()->route('products.index')
            ->with('message', 'Produto atualizado com sucesso!');
    }

    /**
     * Alterna o status de ativação (is_active).
     */
    public function toggle(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('message', 'Status de ativação atualizado!');
    }

    /**
     * Alterna o status de destaque (is_featured). ⭐
     */
    public function toggleFeatured(Product $product)
    {
        $this->repository->toggleFeatured($product);
        return back()->with('message', 'Status de destaque atualizado!');
    }

    /**
     * Remove o produto e seus arquivos.
     */
    public function destroy(Product $product)
    {
        $this->service->deleteProduct($product);
        return redirect()->route('products.index')->with('message', 'Removido com sucesso.');
    }

    /**
     * Renderiza a visualização prévia do produto.
     */
    public function preview(Product $product)
    {
        $product->load(['supplier', 'images']);

        return Inertia::render('Products/Preview', [
            'product' => $product
        ]);
    }
}