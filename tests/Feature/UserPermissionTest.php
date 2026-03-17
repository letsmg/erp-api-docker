<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_acessar_area_admin()
    {
        $admin = User::factory()->create([
            'access_level' => 1
        ]);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
    }

    public function test_usuario_padrao_nao_pode_acessar_area_admin()
    {
        $user = User::factory()->create([
            'access_level' => 0
        ]);

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(403); // ou redirect, depende da sua regra
    }

    public function test_usuario_padrao_nao_pode_criar_admin()
    {
        $user = User::factory()->create([
            'access_level' => 0
        ]);

        $response = $this->actingAs($user)->post('/users', [
            'name' => 'Novo Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456'),
            'access_level' => 1
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_pode_criar_admin()
    {
        $admin = User::factory()->create([
            'access_level' => 1
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Novo Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456'),
            'access_level' => 1
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@test.com',
            'access_level' => 1
        ]);
    }

    public function test_usuario_padrao_nao_pode_deletar_usuario()
    {
        $user = User::factory()->create([
            'access_level' => 0
        ]);

        $target = User::factory()->create();

        $response = $this->actingAs($user)->delete("/users/{$target->id}");

        $response->assertStatus(403);
    }

    public function test_admin_pode_deletar_usuario()
    {
        $admin = User::factory()->create([
            'access_level' => 1
        ]);

        $target = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/users/{$target->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'id' => $target->id
        ]);
    }
}