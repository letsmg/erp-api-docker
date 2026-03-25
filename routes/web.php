<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ClientController; // Adicionado
use App\Http\Controllers\SaleController;   // Adicionado
use App\Http\Controllers\MessageController; // Adicionado
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. VITRINE (Pública para todos - Visitantes e Logados)
// Nota: Movi para fora do 'guest' para que o Admin logado também veja a loja.
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/', [StoreController::class, 'index'])->name('store.index');
Route::get('/store/product/{id}', [StoreController::class, 'show'])->name('store.product');
Route::post('/terms/accept', [StoreController::class, 'acceptTerms'])->name('store.terms.accept');

// 2. AUTENTICAÇÃO (Apenas para quem NÃO está logado)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');    
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    
    // Esqueci Senha
    Route::get('/forgot-password', [LoginController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [LoginController::class, 'sendResetLinkEmail'])->name('password.email');    
});

// 3. PAINEL ADMINISTRATIVO (Protegido por login)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard e Logout
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    
    // Sua rota original de preview do Admin
    Route::get('/products/{product}/preview', [ProductController::class, 'preview'])->name('products.preview');
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');    
    Route::patch('/products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    // Gerenciamento de Produtos
    Route::resource('products', ProductController::class);

    // Outros Recursos (Recuperando os Resources que você tinha)
    Route::resource('suppliers', SupplierController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('sales', SaleController::class);

    // Relatórios
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');

    // Usuários
    Route::resource('users', UserController::class);    
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

    // Mensagens
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');

    // 4. ÁREA DO SUPER-ADMIN
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {         
        // Espaço para rotas exclusivas de Admin Master
    });
});