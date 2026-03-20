<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seeders opcionais se precisar de dados adicionais
        //$this->seed();
    }

    /** --- TESTES DE ACESSO --- **/

    public function test_admin_pode_acessar_dashboard()
    {
        $admin = User::factory()->create([
            'access_level' => 1,
            'is_active' => true
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_tela_de_login_esta_acessivel()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_usuario_nao_autenticado_e_redirecionado_para_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_pode_fazer_logout()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** --- TESTES DE PERMISSÃO --- **/

    public function test_admin_pode_acessar_lista_de_usuarios()
    {
        $admin = User::factory()->create(['access_level' => 1]);
        $response = $this->actingAs($admin)->get(route('users.index'));
        $response->assertStatus(200);
    }

    public function test_usuario_comum_pode_visualizar_usuarios_nivel_0()
    {
        $user = User::factory()->create(['access_level' => 0]);
        $outroUsuario = User::factory()->create(['access_level' => 0]);

        $response = $this->actingAs($user)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertSee($outroUsuario->name);
    }

    public function test_usuario_comum_pode_resetar_senha_outro_nivel_0()
    {
        $user = User::factory()->create(['access_level' => 0]);
        $outroUsuario = User::factory()->create(['access_level' => 0]);

        $response = $this->actingAs($user)->patch(route('users.reset', $outroUsuario));

        // Verifica que houve redirect (302)
        $response->assertRedirect();

        // Verifica que a mensagem de sucesso foi enviada na sessão
        $response->assertSessionHas('message', 'Senha resetada para: Mudar@123');
    }

    public function test_admin_pode_cadastrar_usuario()
    {
        $admin = User::factory()->create(['access_level' => 1]);
        $senhaForte = 'Senha@Forte123';

        $novoUsuario = [
            'name' => 'Clone',
            'email' => 'clone@teste.com',
            'password' => $senhaForte,
            'password_confirmation' => $senhaForte,
            'access_level' => 0,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $novoUsuario);

        if ($response->status() !== 302) {
            dump($response->getSession()->get('errors')->getMessages());
        }

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'clone@teste.com',
            'is_active' => 1
        ]);
    }

    public function test_usuario_comum_nao_pode_deletar_ninguem()
    {
        $user = User::factory()->create(['access_level' => 0]);

        // Tenta deletar um usuário admin (nível 1)
        $alvoAdmin = User::factory()->create(['access_level' => 1]);
        $response = $this->actingAs($user)->delete(route('users.destroy', $alvoAdmin));

        $response->assertRedirect();
        $response->assertSessionHasErrors(function ($errors) {
            return isset($errors['code']) && $errors['code'] === 403;
        });
        $this->assertDatabaseHas('users', ['id' => $alvoAdmin->id]);

        // Tenta deletar outro usuário nível 0
        $alvoNivel0 = User::factory()->create(['access_level' => 0]);
        $response = $this->actingAs($user)->delete(route('users.destroy', $alvoNivel0));

        $response->assertRedirect();
        $response->assertSessionHasErrors(function ($errors) {
            return isset($errors['code']) && $errors['code'] === 403;
        });
        $this->assertDatabaseHas('users', ['id' => $alvoNivel0->id]);
    }
}