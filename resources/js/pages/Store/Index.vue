<script setup>
import { onMounted, onUnmounted } from 'vue'; // Importações necessárias para o timer
import { Head, Link } from '@inertiajs/vue3';
import { useStoreIndex } from './useStoreIndex';
import { Search, SlidersHorizontal, ShoppingBag, ChevronLeft, ChevronRight, Tag, Percent } from 'lucide-vue-next';

const props = defineProps({
    products: Object,
    featuredProducts: Array,
    onSaleProducts: Array,
    ads: Array,
    brands: Array
});

const { search, minPrice, maxPrice, brand } = useStoreIndex(props);

// Variável para armazenar o intervalo do timer
let timer = null;

// Função de scroll melhorada com lógica de "Loop"
const scroll = (id, direction) => {
    const el = document.getElementById(id);
    if (el) {
        const isAtEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 10;
        
        if (direction === 'right' && isAtEnd) {
            // Se estiver no fim e for para a direita, volta ao início
            el.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            // Caso contrário, faz o scroll normal
            const offset = direction === 'left' ? -el.offsetWidth : el.offsetWidth;
            el.scrollBy({ left: offset, behavior: 'smooth' });
        }
    }
};

// Funções para controlar o Autoplay
const startAutoplay = () => {
    stopAutoplay(); // Garante que não existam dois timers rodando
    timer = setInterval(() => {
        scroll('hero-carousel', 'right');
    }, 7000); // 5000ms = 5 segundos
};

const stopAutoplay = () => {
    if (timer) clearInterval(timer);
};

// Ciclo de vida do componente
onMounted(() => {
    startAutoplay();
});

onUnmounted(() => {
    stopAutoplay();
});
</script>

<template>
    <Head title="Vitrine Premium" />

    <div class="min-h-screen bg-gradient-to-b from-slate-200 to-slate-100 text-slate-900 font-sans pb-20">
        
        <nav class="sticky top-0 z-50 bg-slate-900 shadow-2xl">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <h1 class="text-2xl font-black tracking-tighter uppercase text-white">
                    NEXUS<span class="text-indigo-500">STORE</span>
                </h1>
                
                <div class="hidden md:flex flex-1 max-w-md mx-10 relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input v-model="search" type="text" placeholder="Buscar na loja..."
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

        <section v-if="featuredProducts?.length" class="max-w-7xl mx-auto px-6 mt-8">
            <div class="relative group">
                <div id="hero-carousel" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide gap-4 rounded-[3rem] shadow-2xl">
                    <div v-for="p in featuredProducts" :key="p.id" 
                         class="min-w-full snap-center relative aspect-[21/9] bg-slate-900 overflow-hidden">
                        <img :src="p.images && p.images[0] ? '/storage/products/' + p.images[0].path : 'https://placehold.co/1200x500'" 
                             class="w-full h-full object-cover opacity-40 transition-transform duration-700 group-hover:scale-105" />
                        <div class="absolute inset-0 flex flex-col justify-center px-12 text-white bg-gradient-to-r from-slate-900 via-slate-900/40 to-transparent">
                            <span class="bg-indigo-600 w-fit px-3 py-1 rounded-full text-[10px] font-black uppercase mb-4 tracking-widest">Destaque</span>
                            <h2 class="text-5xl font-black mb-2 tracking-tighter">{{ p.description }}</h2>
                            <p class="text-xl text-slate-300 mb-6 font-medium">Lançamento por R$ {{ p.sale_price }}</p>
                            <button class="bg-white text-slate-900 px-8 py-4 rounded-2xl font-black uppercase text-xs w-fit hover:bg-indigo-600 hover:text-white transition shadow-xl">Ver Produto</button>
                        </div>
                    </div>
                </div>
                <button @click="scroll('hero-carousel', 'left')" class="absolute left-6 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white text-white hover:text-black p-4 rounded-full backdrop-blur-md transition hidden group-hover:block border border-white/20">
                    <ChevronLeft/>
                </button>
                <button @click="scroll('hero-carousel', 'right')" class="absolute right-6 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white text-white hover:text-black p-4 rounded-full backdrop-blur-md transition hidden group-hover:block border border-white/20">
                    <ChevronRight/>
                </button>
            </div>
        </section>

        <section v-if="ads?.length" class="max-w-7xl mx-auto px-6 mt-12">
            <div class="bg-gradient-to-r from-rose-500 to-rose-400 rounded-[2.5rem] p-8 flex items-center justify-between text-white shadow-xl shadow-rose-900/20 relative overflow-hidden group">
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <Percent class="w-6 h-6 text-rose-100" />
                        <span class="text-xs font-black uppercase tracking-[0.3em] text-rose-50">Oferta Especial</span>
                    </div>
                    
                    <h3 class="text-3xl font-black italic tracking-tighter uppercase">
                        USE O CUPOM: 
                        <span class="bg-white text-rose-700 px-4 py-1 rounded-xl ml-2 shadow-lg not-italic">PRIMEIRA10</span>
                    </h3>
                </div>

                <div class="absolute right-0 opacity-50 text-7xl font-black -rotate-12 select-none group-hover:scale-110 transition duration-1000 italic text-rose-200">
                    Anuncie!
                </div>
            </div>
        </section>

        <section v-if="onSaleProducts?.length" class="max-w-7xl mx-auto px-6 mt-12">
            <div class="flex items-center justify-between mb-6 px-2">
                <h4 class="text-xl font-black uppercase tracking-tighter flex items-center gap-2">
                    <Tag class="text-indigo-600 w-5 h-5" /> Ofertas Relâmpago
                </h4>
                <div class="flex gap-2">
                    <button @click="scroll('sale-carousel', 'left')" class="p-3 bg-white border border-slate-200 rounded-2xl hover:bg-slate-900 hover:text-white transition shadow-sm active:scale-95 text-slate-600">
                        <ChevronLeft class="w-5 h-5"/>
                    </button>
                    <button @click="scroll('sale-carousel', 'right')" class="p-3 bg-white border border-slate-200 rounded-2xl hover:bg-slate-900 hover:text-white transition shadow-sm active:scale-95 text-slate-600">
                        <ChevronRight class="w-5 h-5"/>
                    </button>
                </div>
            </div>
            <div id="sale-carousel" class="flex overflow-x-auto snap-x scrollbar-hide gap-6 pb-6">
                <div v-for="p in onSaleProducts" :key="p.id" 
                     class="min-w-[280px] snap-start bg-white p-5 rounded-[2.5rem] shadow-lg border border-white hover:border-indigo-100 transition-colors group">
                    <div class="aspect-square rounded-[1.8rem] overflow-hidden mb-4 bg-slate-100 relative">
                        <img :src="p.images && p.images[0] ? '/storage/products/' + p.images[0].path : 'https://placehold.co/400x400'" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" />
                        <div class="absolute top-3 right-3 bg-indigo-600 text-white text-[9px] font-black px-3 py-1 rounded-full uppercase shadow-lg shadow-indigo-200">Promo</div>
                    </div>
                    <h5 class="font-black text-[11px] uppercase truncate text-slate-700 tracking-tight">{{ p.description }}</h5>
                    <p class="text-lg font-black text-indigo-600 mt-2 font-mono">R$ {{ p.sale_price }}</p>
                </div>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-6 py-16 flex flex-col md:flex-row gap-12">
            <aside class="w-full md:w-64">
                <div class="bg-white p-7 rounded-[2.5rem] border border-slate-200 shadow-xl sticky top-28 space-y-8">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <SlidersHorizontal class="w-3 h-3" /> Filtrar Por
                    </h3>
                    
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase text-slate-500 tracking-wider">Faixa de Preço</label>
                            <div class="flex gap-2">
                                <input v-model="maxPrice" type="number" placeholder="Até R$" class="w-full bg-slate-50 border-slate-100 rounded-xl text-xs font-bold p-3 focus:ring-slate-900 outline-none" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase text-slate-500 tracking-wider">Marca</label>
                            <select v-model="brand" class="w-full bg-slate-50 border-slate-100 rounded-xl text-xs font-bold p-3 focus:ring-slate-900 outline-none">
                                <option value="">Todas</option>
                                <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="flex-1 flex flex-col">
                
                <div v-if="products.links && products.links.length > 3" class="mb-10 flex justify-center">
                    <div class="flex items-center gap-1 bg-slate-900 p-1.5 rounded-2xl shadow-xl border border-slate-800">
                        <template v-for="(link, k) in products.links" :key="k">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" preserve-scroll
                                  class="px-4 py-2 text-[10px] font-black uppercase rounded-xl transition-all duration-300"
                                  :class="link.active 
                                    ? 'bg-indigo-600 text-white shadow-lg' 
                                    : 'text-slate-400 hover:text-white hover:bg-slate-800'" />
                        </template>
                    </div>
                </div>

                <div v-if="products.data && products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="product in products.data" :key="product.id" 
                         class="group bg-white p-5 rounded-[3rem] border border-white shadow-md hover:shadow-2xl transition-all duration-500">
                        <div class="relative aspect-[4/5] rounded-[2.2rem] overflow-hidden bg-slate-100 mb-5 shadow-inner">
                            <img :src="product.images && product.images[0] ? '/storage/products/' + product.images[0].path : 'https://placehold.co/600x800'" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-700" />
                            <div class="absolute bottom-4 left-4 right-4 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all">
                                <button class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase text-[10px] shadow-xl hover:bg-indigo-600 transition flex items-center justify-center gap-2">
                                    <ShoppingBag class="w-4 h-4"/> Adicionar
                                </button>
                            </div>
                        </div>
                        <h3 class="text-sm font-black uppercase truncate text-slate-800 tracking-tight">{{ product.description }}</h3>
                        <p class="text-xl font-black text-indigo-600 mt-2 tracking-tighter font-mono">R$ {{ product.sale_price }}</p>
                    </div>
                </div>

                <div v-if="products.links && products.links.length > 3" class="mt-20 flex justify-center">
                    <div class="flex items-center gap-1 bg-slate-900 p-2 rounded-[2.5rem] shadow-2xl border border-slate-800">
                        <template v-for="(link, k) in products.links" :key="k">
                            <div v-if="link.url === null" 
                                 class="px-5 py-2.5 text-slate-600 text-[10px] font-black uppercase opacity-50 select-none"
                                 v-html="link.label" />
                            
                            <Link v-else :href="link.url" v-html="link.label" preserve-scroll
                                  class="px-5 py-2.5 text-[10px] font-black uppercase rounded-[1.8rem] transition-all duration-300"
                                  :class="link.active 
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/40' 
                                    : 'text-slate-400 hover:text-white hover:bg-slate-800'" />
                        </template>
                    </div>
                </div>

            </section>
        </main>
    </div>
</template>

<style scoped>
/* Esconde a barra de rolagem mas mantém a funcionalidade */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>