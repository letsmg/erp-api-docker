<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Support\Facades\Log;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function storeProduct(array $data, $request)
    {
        return DB::transaction(function () use ($data, $request) {
            $data['is_active'] = false;
            $data['slug'] = $this->generateSlug($data['description'] ?? '');
            
            $product = $this->repository->create($data);

            if ($request->hasFile('images')) {
                $this->handleImageUpload($product, $request->file('images'));
            }

            $this->syncSeo($product, $request->all());

            return $product;
        });
    }

    public function updateProduct(Product $product, array $data, $request)
    {
        return DB::transaction(function () use ($product, $data, $request) {
            // 1. Gestão de Imagens (Remoção)
            // Filtramos as imagens que devem permanecer baseadas no array enviado pelo Vue
            $existingIds = collect($data['existing_images'] ?? [])->pluck('id')->toArray();
            
            $imagesToDelete = $product->images()->whereNotIn('id', $existingIds)->get();

            foreach ($imagesToDelete as $oldImg) {
                Storage::disk('public')->delete('products/' . $oldImg->path);
                $oldImg->delete();
            }

            // 2. Atualizar Ordem das Imagens (Usando a coluna 'order')
            foreach ($data['existing_images'] ?? [] as $index => $imageData) {
                ProductImage::where('id', $imageData['id'])
                    ->update(['order' => $index]);
            }
            
            // 3. Novas Imagens
            if ($request->hasFile('new_images')) {
                // Pegamos a última ordem ocupada para continuar a sequência
                $lastOrder = $product->images()->max('order') ?? -1;
                $this->handleImageUpload($product, $request->file('new_images'), $lastOrder + 1);
            }

            // 4. Atualizar Slug se a descrição mudou
            if (isset($data['description']) && $product->description !== $data['description']) {
                $data['slug'] = $this->generateSlug($data['description']);
            }

            // 5. Update dos dados básicos via repositório
            $product = $this->repository->update($product, $data);
            
            // 6. Sincronizar SEO
            $this->syncSeo($product, $request->all());

            return $product;
        });
    }

    public function deleteProduct(Product $product)
    {
        return DB::transaction(function () use ($product) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete('products/' . $img->path);
                $img->delete();
            }

            if ($product->seo) {
                $product->seo->delete();
            }

            return $this->repository->delete($product);
        });
    }

    private function generateSlug($description)
    {
        return Str::slug($description) . '-' . Str::lower(Str::random(5));
    }

    private function handleImageUpload($product, array $files, $startOrder = 0)
    {
        foreach ($files as $index => $file) {
            if (!$this->isImageSafe($file)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'images' => 'Uma das imagens contém conteúdo impróprio.'
                ]);
            }
            
            $path = $file->store('products', 'public');
            
            // Cria via relacionamento usando a coluna 'order'
            $product->images()->create([
                'path' => basename($path),
                'order' => $startOrder + $index
            ]);
        }
    }

    private function syncSeo($product, array $input)
    {
        $seoFields = [
            'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 
            'h1', 'text1', 'h2', 'text2', 'schema_markup', 'google_tag_manager', 'ads'
        ];

        // Filtra apenas os campos de SEO que vieram no request
        $data = collect($input)->only($seoFields)->toArray();
        
        // Verifica se algum campo de SEO foi preenchido
        $hasValue = collect($data)->some(fn($v) => !empty($v));

        if ($hasValue || $product->seo()->exists()) {
            // Usamos o relacionamento polimórfico definido no Model Product
            // O Laravel cuidará de preencher seoable_id e seoable_type automaticamente
            $product->seo()->updateOrCreate(
                [], // Deixamos vazio para ele buscar pela relação pai (product_id)
                array_merge($data, ['slug' => $product->slug])
            );
        }
    }

    private function isImageSafe($image)
    {
        $credentialPath = base_path('google-credentials.json');
        
        // Verifica se a biblioteca e o arquivo de credenciais existem
        if (!class_exists('Google\Cloud\Vision\V1\ImageAnnotatorClient') || !file_exists($credentialPath)) {
            return true; 
        }

        try {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialPath);
            $imageAnnotator = new ImageAnnotatorClient();
            $content = file_get_contents($image->getRealPath());
            $response = $imageAnnotator->safeSearchDetection($content);
            $safe = $response->getSafeSearchAnnotation();
            $imageAnnotator->close();

            // 3: Likely, 4: Very Likely, 5: Unlikely (depende da versão, mas geralmente Likely+ é bloqueado)
            $unsafeLevels = [4, 5]; 
            return !(in_array($safe->getAdult(), $unsafeLevels) || in_array($safe->getViolence(), $unsafeLevels));
        } catch (\Exception $e) {
            Log::error("Erro API Vision: " . $e->getMessage());
            return true;
        }
    }
    
}