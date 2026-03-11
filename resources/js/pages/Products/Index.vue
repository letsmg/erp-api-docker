<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { PackagePlus, Search, Edit, Trash2, Globe } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps({ products: Array });
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Gerenciamento de Produtos</h2>
            
            <Link 
                :href="route('products.create')" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-blue-700 transition"
            >
                <PackagePlus class="w-5 h-5" />
                Novo Produto
            </Link>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="p-4 font-semibold text-gray-600">Descrição</th>
                        <th class="p-4 font-semibold text-gray-600">Preço</th>
                        <th class="p-4 font-semibold text-gray-600">Estoque</th>
                        <th class="p-4 font-semibold text-gray-600">SEO / Vitrine</th>
                        <th class="p-4 font-semibold text-gray-600 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id" class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="p-4 text-gray-700 font-medium">{{ product.description }}</td>
                        <td class="p-4 text-gray-600">R$ {{ product.sale_price }}</td>
                        <td class="p-4 text-gray-600">{{ product.stock_quantity }} un</td>
                        <td class="p-4">
                            <span v-if="product.seo_title" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full flex items-center w-fit gap-1">
                                <Globe class="w-3 h-3" /> Otimizado
                            </span>
                            <span v-else class="text-xs bg-gray-100 text-gray-400 px-2 py-1 rounded-full">Padrão</span>
                        </td>
                        <td class="p-4 flex justify-center gap-3">
                            <button class="text-blue-500 hover:text-blue-700"><Edit class="w-5 h-5" /></button>
                            <button class="text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5" /></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>