<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Inertia\Inertia;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    protected AuthService $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    // Unificamos showLogin e showLoginForm aqui
    public function showLogin()
    {
        return Inertia::render('Auth/Login', [
            'userIp' => request()->ip(),
            'status' => session('status'),
        ]);
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if ($this->service->login($credentials, $request->boolean('remember'))) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas ou conta bloqueada.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->service->logout($request);
        return redirect('/');
    }

    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->service->sendResetLink($data['email']);
            return back()->with('success', 'Link enviado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Erro no provedor de e-mail: ' . $e->getMessage()
            ]);
        }
    }
    
    // Remova o método showLoginForm() duplicado se ele existir no final do arquivo
}