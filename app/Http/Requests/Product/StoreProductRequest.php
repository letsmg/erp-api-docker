<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Dados Básicos
            'supplier_id'     => 'required|exists:suppliers,id',
            'description'     => 'required|string|max:255',            
            'brand'           => 'nullable|string|max:100',
            'model'           => 'nullable|string|max:100',
            'size'            => 'nullable|string|max:50',
            'collection'      => 'nullable|string|max:100',
            'gender'          => 'nullable|string|max:50',
            'barcode'         => 'nullable|string|max:100',
            'cost_price'      => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',

            // Financeiro / Promoção
            'promo_price'     => 'nullable|numeric|min:0',
            'promo_start_at'  => 'nullable|date',
            'promo_end_at'    => 'nullable|date|after_or_equal:promo_start_at',

            // Logística e Frete
            'weight' => 'required|numeric|min:0',
            'width'  => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
            'length'          => 'required|numeric|min:1',
            'free_shipping'   => 'boolean',
            
            // Marketing & SEO
            'meta_title'        => 'nullable|string|max:70',
            'meta_description'  => 'nullable|string|max:160',
            'meta_keywords'     => 'nullable|string',
            'canonical_url'     => 'nullable|string', // String em vez de URL para aceitar sem http://
            'h1'                => 'nullable|string',
            'text1'             => 'nullable|string',
            'h2'                => 'nullable|string',
            'text2'             => 'nullable|string',
            'schema_markup'     => 'nullable|string',
            'google_tag_manager'=> 'nullable|string',
            'ads'               => 'nullable|string',
            
            // Imagens
            'images'          => 'required|array|min:1|max:6',
            'images.*'        => 'image|mimes:jpg,jpeg,png,webp|max:2048', 
        ];
    }

    
    public function messages(): array
    {
        return [
            // Dados Básicos
            'supplier_id.required' => 'Selecione um fornecedor para este produto.',
            'supplier_id.exists'   => 'O fornecedor selecionado é inválido.',
            'description.required' => 'A descrição do produto é obrigatória.',
            'description.max'      => 'A descrição não deve ultrapassar 255 caracteres.',
            'cost_price.required'  => 'Informe o preço de custo.',
            'sale_price.required'  => 'Informe o preço de venda.',
            'stock_quantity.required' => 'Informe a quantidade em estoque.',

            // Logística e Frete
            'weight.required' => 'O peso é obrigatório para o cálculo de frete.',
            'weight.min'      => 'O peso deve ser maior que zero (mínimo 0.001kg).',
            'width.required'  => 'A largura é obrigatória.',
            'width.min'       => 'A largura deve ser de no mínimo 1cm.',
            'height.required' => 'A altura é obrigatória.',
            'height.min'      => 'A altura deve ser de no mínimo 1cm.',
            'length.required' => 'O comprimento é obrigatório.',
            'length.min'      => 'O comprimento deve ser de no mínimo 1cm.',

            // Promoção
            'promo_price.numeric' => 'O preço promocional deve ser um número válido.',
            'promo_start_at.date' => 'Informe uma data de início válida.',
            'promo_end_at.date'   => 'Informe uma data de término válida.',
            'promo_end_at.after_or_equal' => 'A data de término da promoção deve ser igual ou posterior ao início.',

            // SEO
            'meta_title.max'       => 'O título SEO não deve ultrapassar 70 caracteres.',
            'meta_description.max' => 'A meta descrição não deve ultrapassar 160 caracteres.',
            'canonical_url.url'    => 'O formato da URL Canônica é inválido.',

            // Imagens
            'images.required' => 'Você precisa enviar pelo menos uma imagem para cadastrar o produto.',
            'images.min'      => 'Envie ao menos :min imagem.',
            'images.max'      => 'Você pode enviar no máximo 6 imagens.',
            'images.*.image'  => 'O arquivo enviado deve ser uma imagem válida.',
            'images.*.mimes'  => 'As imagens devem ser do tipo: jpg, jpeg, png ou webp.',
            'images.*.max'    => 'Cada imagem não pode ser maior que 2MB.',
            
            // Mensagens para o Update (Imagens Existentes)
            'new_images.max'  => 'Você pode carregar no máximo 6 novas imagens.',
            'existing_images.*.id.exists' => 'Uma das imagens enviadas não foi encontrada no banco de dados.',
        ];
    }
}