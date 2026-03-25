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

    /**
     * Orquestra a criação do produto, imagens e SEO.
     */
    public function storeProduct(array $data, $request)
    {
        return DB::transaction(function () use ($data, $request) {
            // Geramos o slug único para o produto
            $data['slug'] = $this->generateSlug($data['description'] ?? '');
            
            // Criamos o produto via repositório (que cuidará da regra do is_active)
            $product = $this->repository->create($data);

            // Upload de imagens se houver
            if ($request->hasFile('images')) {
                $this->handleImageUpload($product, $request->file('images'));
            }

            // Sincronização de metadados SEO
            $this->syncSeo($product, $request->all());

            return $product;
        });
    }

    /**
     * Gerencia a atualização de dados, reordenação/remoção de fotos e SEO.
     */
    public function updateProduct(Product $product, array $data, $request)
    {
        return DB::transaction(function () use ($product, $data, $request) {
            // 1. Limpeza de imagens removidas no front-end
            $existingIds = collect($data['existing_images'] ?? [])->pluck('id')->toArray();
            $imagesToDelete = $product->images()->whereNotIn('id', $existingIds)->get();

            foreach ($imagesToDelete as $oldImg) {
                Storage::disk('public')->delete('products/' . $oldImg->path);
                $oldImg->delete();
            }

            // 2. Atualização da ordem das imagens existentes
            foreach ($data['existing_images'] ?? [] as $index => $imageData) {
                ProductImage::where('id', $imageData['id'])->update(['order' => $index]);
            }
            
            // 3. Processamento de novas imagens
            if ($request->hasFile('new_images')) {
                $lastOrder = $product->images()->max('order') ?? -1;
                $this->handleImageUpload($product, $request->file('new_images'), $lastOrder + 1);
            }

            // 4. Atualização do Slug caso a descrição mude
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

    /**
     * Remove o produto e limpa arquivos físicos.
     */
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
                    'images' => 'Uma das imagens contém conteúdo impróprio detectado pela IA.'
                ]);
            }
            
            $path = $file->store('products', 'public');
            
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
            'h1', 'text1', 'h2', 'text2', 'schema_markup', 'google_tag_manager', 
        ];

        $data = collect($input)->only($seoFields)->toArray();
        $hasValue = collect($data)->some(fn($v) => !empty($v));

        if ($hasValue || $product->seo()->exists()) {
            $product->seo()->updateOrCreate(
                [], 
                array_merge($data, ['slug' => $product->slug])
            );
        }
    }

    private function isImageSafe($image)
    {
        $credentialPath = base_path('google-credentials.json');
        
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

            $unsafeLevels = [4, 5]; // Likely e Very Likely
            return !(in_array($safe->getAdult(), $unsafeLevels) || in_array($safe->getViolence(), $unsafeLevels));
        } catch (\Exception $e) {
            Log::error("Erro API Vision: " . $e->getMessage());
            return true;
        }
    }
}