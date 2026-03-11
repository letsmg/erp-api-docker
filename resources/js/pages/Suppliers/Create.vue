<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft, Building2, Phone, MapPin, Hash, Globe, User, XCircle } from 'lucide-vue-next';
import { onMounted, onUnmounted } from 'vue';
import { fillFormData, clearFormData } from '@/lib/utils';

const form = useForm({
    company_name: '',
    cnpj: '',
    state_registration: '',
    address: '',
    neighborhood: '',
    city: '',
    zip_code: '',
    contact_name_1: '',
    phone_1: '',
});

// Funções para os atalhos mágicos
const filler = () => fillFormData(form);
const clearer = () => clearFormData(form);

// Máscara de CNPJ (00.000.000/0000-00)
const maskCNPJ = (e) => {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
    v = v.replace(/(\d{4})(\d)/, '$1-$2');
    form.cnpj = v.substring(0, 18);
};

// Máscara de CEP (00000-000)
const maskCEP = (e) => {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/^(\d{5})(\d)/, '$1-$2');
    form.zip_code = v.substring(0, 9);
};

const submit = () => {
    form.post(route('suppliers.store'), {
        onSuccess: () => form.reset(),
    });
};

onMounted(() => {
    window.addEventListener('magic-fill', filler);
    window.addEventListener('magic-clear', clearer);
});

onUnmounted(() => {
    window.removeEventListener('magic-fill', filler);
    window.removeEventListener('magic-clear', clearer);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Novo Fornecedor" />

        <div class="max-w-4xl mx-auto pb-10">
            <div class="mb-6 flex items-center justify-between">
                <Link :href="route('suppliers.index')" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center transition">
                    <ArrowLeft class="w-4 h-4 mr-1" /> Voltar
                </Link>
                <h2 class="text-xl font-bold text-gray-800">Cadastrar Fornecedor</h2>
            </div>

            <Transition
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="-translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div v-if="form.hasErrors" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                    <div class="flex items-center mb-2">
                        <XCircle class="w-5 h-5 text-red-500 mr-2" />
                        <span class="text-sm font-black text-red-800 uppercase tracking-tighter">Ops! Verifique os campos:</span>
                    </div>
                    <ul class="list-disc list-inside">
                        <li v-for="(error, field) in form.errors" :key="field" class="text-xs text-red-600 font-bold uppercase tracking-tight">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </Transition>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Razão Social</label>
                        <div class="relative flex items-center">
                            <Building2 class="absolute left-3 w-4 h-4 text-gray-400" />
                            <input 
                                v-model="form.company_name" 
                                type="text" 
                                :class="{'border-red-500 bg-red-50': form.errors.company_name}"
                                class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                required 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CNPJ</label>
                        <div class="relative flex items-center">
                            <Hash class="absolute left-3 w-4 h-4 text-gray-400" />
                            <input 
                                :value="form.cnpj" 
                                @input="maskCNPJ" 
                                type="text" 
                                :class="{'border-red-500 bg-red-50': form.errors.cnpj}"
                                class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                placeholder="00.000.000/0000-00" 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Inscrição Estadual</label>
                        <div class="relative flex items-center">
                            <Globe class="absolute left-3 w-4 h-4 text-gray-400" />
                            <input 
                                v-model="form.state_registration" 
                                type="text" 
                                :class="{'border-red-500 bg-red-50': form.errors.state_registration}"
                                class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                            />
                        </div>
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Endereço</label>
                            <div class="relative flex items-center">
                                <MapPin class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input 
                                    v-model="form.address" 
                                    type="text" 
                                    :class="{'border-red-500 bg-red-50': form.errors.address}"
                                    class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CEP</label>
                            <input 
                                :value="form.zip_code" 
                                @input="maskCEP" 
                                type="text" 
                                :class="{'border-red-500 bg-red-50': form.errors.zip_code}"
                                class="w-full rounded-lg border-gray-200 focus:ring-indigo-500 text-center" 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cidade</label>
                        <input 
                            v-model="form.city" 
                            type="text" 
                            :class="{'border-red-500 bg-red-50': form.errors.city}"
                            class="w-full rounded-lg border-gray-200 focus:ring-indigo-500" 
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bairro</label>
                        <input 
                            v-model="form.neighborhood" 
                            type="text" 
                            :class="{'border-red-500 bg-red-50': form.errors.neighborhood}"
                            class="w-full rounded-lg border-gray-200 focus:ring-indigo-500" 
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nome do Contato</label>
                        <div class="relative flex items-center">
                            <User class="absolute left-3 w-4 h-4 text-gray-400" />
                            <input 
                                v-model="form.contact_name_1" 
                                type="text" 
                                :class="{'border-red-500 bg-red-50': form.errors.contact_name_1}"
                                class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Telefone</label>
                        <div class="relative flex items-center">
                            <Phone class="absolute left-3 w-4 h-4 text-gray-400" />
                            <input 
                                v-model="form.phone_1" 
                                type="text" 
                                :class="{'border-red-500 bg-red-50': form.errors.phone_1}"
                                class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end italic text-[10px] text-gray-400 mb-2 uppercase tracking-tighter">
                    Atalhos: <span class="font-bold text-indigo-400 mx-1 underline">CTRL+SHIFT+P</span> Popular | <span class="font-bold text-red-400 mx-1 underline">CTRL+SHIFT+L</span> Limpar
                </div>

                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="bg-indigo-600 text-white px-10 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-indigo-700 transition shadow-lg disabled:opacity-50"
                    >
                        <Save class="w-5 h-5" /> {{ form.processing ? 'Salvando...' : 'Salvar Fornecedor' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>