<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft, Building2, Hash, Mail, Phone, MapPin, XCircle, User, Map } from 'lucide-vue-next';

const props = defineProps({ 
    supplier: Object 
});

const form = useForm({
    // Atualizado para bater com o seu $fillable
    company_name: props.supplier.company_name,
    cnpj: props.supplier.cnpj,
    state_registration: props.supplier.state_registration,
    address: props.supplier.address,
    neighborhood: props.supplier.neighborhood,
    city: props.supplier.city,
    zip_code: props.supplier.zip_code,
    contact_name_1: props.supplier.contact_name_1,
    phone_1: props.supplier.phone_1,
    contact_name_2: props.supplier.contact_name_2,
    phone_2: props.supplier.phone_2,
});

const submit = () => {
    form.put(route('suppliers.update', props.supplier.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Editar Fornecedor" />

        <div class="max-w-4xl mx-auto pb-12">
            <div class="mb-6 flex items-center justify-between">
                <Link :href="route('suppliers.index')" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center transition">
                    <ArrowLeft class="w-4 h-4 mr-1" /> Voltar
                </Link>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-3 py-1 rounded-full">
                    ID: #{{ supplier.id }}
                </span>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-8">
                    <h3 class="text-sm font-black text-gray-400 uppercase mb-6 flex items-center gap-2">
                        <Building2 class="w-4 h-4" /> Informações da Empresa
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Razão Social</label>
                            <div class="relative flex items-center">
                                <Building2 class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input v-model="form.company_name" type="text" class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" required />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CNPJ</label>
                            <div class="relative flex items-center">
                                <Hash class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input v-model="form.cnpj" type="text" class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" required />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Inscrição Estadual</label>
                            <div class="relative flex items-center">
                                <Hash class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input v-model="form.state_registration" type="text" class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" required />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-8">
                    <h3 class="text-sm font-black text-gray-400 uppercase mb-6 flex items-center gap-2">
                        <MapPin class="w-4 h-4" /> Endereço
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Logradouro (Rua, Nº)</label>
                            <input v-model="form.address" type="text" class="w-full rounded-lg border-gray-200 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bairro</label>
                            <input v-model="form.neighborhood" type="text" class="w-full rounded-lg border-gray-200 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cidade</label>
                            <input v-model="form.city" type="text" class="w-full rounded-lg border-gray-200 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CEP</label>
                            <input v-model="form.zip_code" type="text" class="w-full rounded-lg border-gray-200 focus:ring-indigo-500" required />
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-8">
                    <h3 class="text-sm font-black text-gray-400 uppercase mb-6 flex items-center gap-2">
                        <Phone class="w-4 h-4" /> Contatos Responsáveis
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50 rounded-lg space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nome Contato Principal</label>
                                <input v-model="form.contact_name_1" type="text" class="w-full border-gray-200 rounded-lg text-sm" required />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Telefone Principal</label>
                                <input v-model="form.phone_1" type="text" class="w-full border-gray-200 rounded-lg text-sm" required />
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg space-y-4 border-dashed border-2 border-gray-200">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nome Contato Secundário</label>
                                <input v-model="form.contact_name_2" type="text" class="w-full border-gray-200 rounded-lg text-sm" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Telefone Secundário</label>
                                <input v-model="form.phone_2" type="text" class="w-full border-gray-200 rounded-lg text-sm" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 items-center">
                    <span v-if="form.recentlySuccessful" class="text-sm text-green-600 font-bold animate-pulse">
                        Dados salvos!
                    </span>
                    <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-10 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg disabled:opacity-50 flex gap-2">
                        <Save class="w-5 h-5" /> Atualizar Fornecedor
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>