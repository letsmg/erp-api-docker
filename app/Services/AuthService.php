<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperarSenhaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Valida a localização do IP. 
     * Se o IP for do exterior, o login é interrompido imediatamente.
     */
    private function validateGeographicAccess(): void
    {
        $ip = request()->ip();

        // Ignora verificação em ambiente local (localhost)
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return;
        }

        try {
            // Consulta a API com timeout de 2 segundos
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode,message");

            if ($response->successful() && $response->json('status') === 'success') {
                $countryCode = $response->json('countryCode');

                // BLOQUEIO: Se o país não for Brasil (BR), impede o acesso
                if ($countryCode !== 'BR') {
                    logger()->warning("Tentativa de login bloqueada: IP estrangeiro detectado", [
                        'ip' => $ip,
                        'pais' => $countryCode,
                        'email' => request('email')
                    ]);

                    throw ValidationException::withMessages([
                        'email' => [
                            'Acesso negado: Este sistema não aceita logins originados fora do Brasil por razões de segurança.',
                            'Access denied: This system only accepts logins from Brazil for security reasons.'
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Falha na API: loga o erro mas permite tentativa para não travar o sistema
            logger()->error("Falha no serviço de verificação de IP: " . $e->getMessage());
        }
    }

    /**
     * Realiza o processo de login com dupla verificação e registro de IP
     */
    public function login(array $credentials, $remember = false)
    {
        // 1. Verifica se o IP é do Brasil
        $this->validateGeographicAccess();

        // 2. Tenta autenticar
        $attempt = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true
        ], $remember);

        // 3. Se logou com sucesso, atualiza o campo last_login_ip no banco
        if ($attempt) {
            $user = Auth::user();
            $user->update([
                'last_login_ip' => request()->ip()
            ]);
        }

        return $attempt;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sendResetLink(string $email)
    {
        $url = route('login');
        Mail::to($email)->send(new RecuperarSenhaMail($url));
    }
}