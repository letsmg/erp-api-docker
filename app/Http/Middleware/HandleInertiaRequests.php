<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Adicionando o SEO Global da Loja com Cache de 1 hora (3600 segundos)
            'store_seo' => cache()->remember('store_seo', 3600, function () {
                // Ajuste o Model e a query conforme sua estrutura de tabela
                return \App\Models\Seo::where('seoable_type', 'App\Models\Store')->first() ?? [
                    'title' => "ERP Vue Laravel",
                    'description' => "Site de portfólio representando um e-commerce construído com Laravel e Vue.js.",
                    'keywords' => "developer php, laravel, vuejs",
                    'h1' => "ERP Vue Laravel"
                ];
            }),
        ]);
    }
}
