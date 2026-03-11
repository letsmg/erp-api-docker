<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Exibe a lista de fornecedores
     */
    public function index()
    {
        return Inertia::render('Suppliers/Index', [
            'suppliers' => Supplier::latest()->get()
        ]);
    }

    /**
     * Exibe o formulário de criação
     */
    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    /**
     * Salva o novo fornecedor no banco
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'       => 'required|string|max:150',
            'cnpj'               => 'required|string|max:18|unique:suppliers,cnpj',
            'state_registration' => 'required|string|max:20',
            'address'            => 'required|string|max:150',
            'neighborhood'       => 'required|string|max:100',
            'city'               => 'required|string|max:100',
            'zip_code'           => 'required|string|max:10',
            'contact_name_1'     => 'required|string|max:100',
            'phone_1'            => 'required|string|max:20',
            'contact_name_2'     => 'nullable|string|max:100',
            'phone_2'            => 'nullable|string|max:20',
        ], [
            'company_name.required'       => 'A Razão Social é obrigatória.',
            'cnpj.required'               => 'O CNPJ é obrigatório.',
            'cnpj.unique'                 => 'Este CNPJ já está cadastrado.',
            'state_registration.required' => 'A Inscrição Estadual é obrigatória.',
        ]);

        Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('message', 'Fornecedor cadastrado com sucesso!');
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Atualiza os dados do fornecedor
     */
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'company_name'       => 'required|string|max:150',
            'cnpj'               => [
                'required', 
                'string', 
                'max:18', 
                Rule::unique('suppliers')->ignore($supplier->id)
            ],
            'state_registration' => 'required|string|max:20',
            'address'            => 'required|string|max:150',
            'neighborhood'       => 'required|string|max:100',
            'city'               => 'required|string|max:100',
            'zip_code'           => 'required|string|max:10',
            'contact_name_1'     => 'required|string|max:100',
            'phone_1'            => 'required|string|max:20',
            'contact_name_2'     => 'nullable|string|max:100',
            'phone_2'            => 'nullable|string|max:20',
        ], [
            'company_name.required' => 'A Razão Social é obrigatória.',
            'cnpj.unique'           => 'Este CNPJ já pertence a outro fornecedor.',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('message', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Exclui um fornecedor
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('message', 'Fornecedor removido com sucesso!');
    }
}