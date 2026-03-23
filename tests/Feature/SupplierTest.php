<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert; // Importação necessária

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    /** 1. TESTES DE ACESSO (PROTEÇÃO) **/

    public function test_usuario_nao_logado_nao_pode_ver_fornecedores()
    {
        $response = $this->get(route('suppliers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_logado_pode_ver_lista_de_fornecedores()
    {
        // 1. Crie o usuário com o nível de acesso que seu sistema exige
        // Se o seu sistema pede nível 1, mude para: ['access_level' => 1]
        $user = User::factory()->create(); 

        $response = $this->actingAs($user)->get(route('suppliers.index'));

        // 2. ADICIONE ISSO TEMPORARIAMENTE para ver o erro real no console se falhar
        $response->dump(); 

        // 3. Verifique o status antes do Inertia
        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Suppliers/Index')
        );
    }

    /** 2. TESTE DE CRIAÇÃO (STORE) **/

    public function test_usuario_pode_cadastrar_fornecedor()
    {
        $user = User::factory()->create();

        $dados = [
            'company_name' => 'Fornecedor de Teste LTDA',
            'cnpj' => '12.345.678/0001-90',
            'state_registration' => '123456789',
            'email' => 'contato@fornecedor.com',
            'address' => 'Rua de Teste, 123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01001-000',
            'contact_name_1' => 'João Silva',
            'phone_1' => '(11) 99999-9999',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->post(route('suppliers.store'), $dados);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['cnpj' => '12.345.678/0001-90']);
    }

    /** 3. TESTE DE EDIÇÃO (UPDATE) **/

    public function test_usuario_pode_atualizar_fornecedor()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create(['company_name' => 'Antigo Nome']);

        $response = $this->actingAs($user)->put(route('suppliers.update', $supplier), [
            'company_name' => 'Novo Nome Atualizado',
            'cnpj' => $supplier->cnpj,
            'state_registration' => $supplier->state_registration,
            'email' => 'novo@email.com',
            'address' => $supplier->address,
            'neighborhood' => $supplier->neighborhood,
            'city' => $supplier->city,
            'state' => $supplier->state,
            'zip_code' => $supplier->zip_code,
            'contact_name_1' => $supplier->contact_name_1,
            'phone_1' => $supplier->phone_1,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        
        // No PostgreSQL ou MySQL, usamos true/false ou 1/0 dependendo da config
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_name' => 'Novo Nome Atualizado',
            'is_active' => false
        ]);
    }

    /** 4. TESTE DE EXCLUSÃO (DELETE) **/

    public function test_usuario_pode_excluir_fornecedor()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($user)->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertModelMissing($supplier);
    }
}