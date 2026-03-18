<script setup>
import { Link } from '@inertiajs/vue3';
import { Search, ShoppingBag, Cloud } from 'lucide-vue-next';

// Recebe o valor da busca do Index
defineProps({
    searchTerm: String
});

// Avisa o Index que o usuário digitou algo
const emit = defineEmits(['update:searchTerm']);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-200 to-slate-100 text-slate-900 font-sans pb-20">
        <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-2 px-6 flex justify-center items-center gap-4 shadow-md">
            <div class="flex items-center gap-2">
                <Cloud class="w-4 h-4 animate-pulse" />
                <span class="text-[10px] font-black uppercase tracking-widest">Infraestrutura Oracle Cloud Ativa</span>
            </div>
            <a href="https://whatismyipaddress.com/ip/147.15.80.52" target="_blank" class="text-[10px] font-bold underline hover:text-orange-100 transition">
                Verificar IP da Instância →
            </a>
        </div>

        <nav class="sticky top-0 z-50 bg-slate-900 shadow-2xl">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link href="/" class="text-2xl font-black tracking-tighter uppercase text-white">
                    NEXUS<span class="text-indigo-500">STORE</span>
                </Link>
                
                <div class="hidden md:flex flex-1 max-w-md mx-10 relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input 
                        :value="searchTerm" 
                        @input="$emit('update:searchTerm', $event.target.value)"
                        type="text" 
                        placeholder="Buscar na loja..."
                        class="w-full bg-slate-800 border-transparent rounded-2xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:bg-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                    />
                </div>

                <div class="flex items-center gap-6">
                    <Link :href="route('login')" class="text-xs font-bold uppercase tracking-widest text-slate-300 hover:text-white transition">Entrar</Link>
                    <button class="bg-indigo-600 text-white p-3 rounded-2xl hover:bg-indigo-500 transition shadow-lg relative">
                        <ShoppingBag class="w-5 h-5" />
                        <span class="absolute -top-1 -right-1 bg-white text-indigo-900 text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold">0</span>
                    </button>
                </div>
            </div>
        </nav>

        <slot />

        <footer class="max-w-7xl mx-auto px-6 mt-20 text-center text-slate-400 text-xs font-bold uppercase tracking-widest border-t border-slate-300 pt-10">
            &copy; 2026 Nexus Store - SaaS Edition
        </footer>
    </div>
</template>