<script setup>
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useStoreIndex } from './useStoreIndex';
import { 
    SlidersHorizontal, ShoppingBag, ChevronLeft, 
    ChevronRight, ShieldCheck, SearchX 
} from 'lucide-vue-next';

const props = defineProps({
    products: Object,
    featuredProducts: Array,
    onSaleProducts: Array,
    brands: Array,
    filters: Object
});

const { 
    search, minPrice, maxPrice, brand,
    showTermsModal, termsAccepted, acceptTerms,
    scroll, seoData 
} = useStoreIndex(props);
</script>

<template>
    <StoreLayout v-model:searchTerm="search">
        <Head>
            <title>{{ seoData.title }}</title>
            <meta name="description" :content="seoData.description" />
        </Head>

        <header class="max-w-7xl mx-auto px-4 md:px-6 pt-10">
            <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tighter uppercase italic leading-none">
                {{ seoData.h1 }}
            </h1>
            <p class="text-slate-400 text-[10px] md:text-xs font-black mt-3 uppercase tracking-[0.4em]">
                {{ seoData.description }}
            </p>
        </header>

        <section v-if="featuredProducts?.length" class="max-w-7xl mx-auto px-4 md:px-6 mt-12">
            <div class="relative group">
                <div id="hero-carousel" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide gap-4 rounded-[2.5rem] md:rounded-[4rem] shadow-2xl">
                    <div v-for="p in featuredProducts" :key="p.id" 
                         class="min-w-full snap-center relative aspect-[16/9] md:aspect-[21/9] bg-slate-900 overflow-hidden">
                        <img :src="p.images?.[0] ? '/storage/products/' + p.images[0].path : 'https://placehold.co/1200x500'" 
                             class="w-full h-full object-cover opacity-40 transition-transform duration-1000 group-hover:scale-110" />
                        
                        <div class="absolute inset-0 flex flex-col justify-center px-10 md:px-20 text-white bg-gradient-to-r from-slate-900 via-slate-900/20 to-transparent">
                            <span class="bg-indigo-600 w-fit px-4 py-1.5 rounded-full text-[10px] font-black uppercase mb-6 tracking-[0.2em]">Destaque da Semana</span>
                            <h2 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter leading-tight max-w-3xl uppercase italic">{{ p.description }}</h2>
                            <p class="text-2xl md:text-3xl text-indigo-400 mb-8 font-mono font-bold">R$ {{ p.sale_price }}</p>
                            
                            <Link :href="route('store.product', p.id)" 
                                  class="bg-white text-slate-900 px-10 py-5 rounded-2xl font-black uppercase text-xs w-fit hover:bg-indigo-600 hover:text-white transition-all shadow-2xl hover:-translate-y-1">
                                Explorar Produto
                            </Link>
                        </div>
                    </div>
                </div>
                <button @click="scroll('hero-carousel', 'left')" class="absolute left-8 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white text-white hover:text-black p-5 rounded-full backdrop-blur-xl transition hidden md:block border border-white/20">
                    <ChevronLeft class="w-6 h-6"/>
                </button>
                <button @click="scroll('hero-carousel', 'right')" class="absolute right-8 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white text-white hover:text-black p-5 rounded-full backdrop-blur-xl transition hidden md:block border border-white/20">
                    <ChevronRight class="w-6 h-6"/>
                </button>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 md:px-6 py-16 flex flex-col md:flex-row gap-12">
            
            <aside class="w-full md:w-72">
                <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm md:sticky md:top-32 space-y-10">
                    <div class="space-y-6">
                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-slate-900 mb-2 block ml-1 tracking-wider">Preço Limite</label>
                            <input v-model="maxPrice" type="number" placeholder="Até R$" 
                                class="w-full bg-slate-50 border-none rounded-2xl text-xs font-bold p-4 focus:ring-2 focus:ring-indigo-500 transition-all" />
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-900 mb-2 block ml-1 tracking-wider">Marca</label>
                            <select v-model="brand" class="w-full bg-slate-50 border-none rounded-2xl text-xs font-bold p-4 focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="">Todas as Marcas</option>
                                <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="flex-1">
                
                <nav v-if="products.links?.length > 3" 
                     class="sticky top-[80px] z-30 py-4 bg-slate-50/90 backdrop-blur-xl mb-10 flex justify-center flex-wrap gap-2 border-b border-slate-100/50 rounded-b-3xl">
                    <Link v-for="(link, k) in products.links" :key="k"
                        :href="link.url || '#'" v-html="link.label"
                        class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase transition-all border shadow-sm"
                        :class="link.active 
                            ? 'bg-slate-900 text-white border-slate-900 scale-105 shadow-lg shadow-slate-200' 
                            : 'bg-white text-slate-400 border-slate-100 hover:bg-slate-50 hover:text-slate-900'"
                    />
                </nav>

                <div v-if="products.data?.length" class="grid grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                    <Link v-for="product in products.data" :key="product.id" 
                          :href="route('store.product', product.id)"
                          class="group bg-white p-5 rounded-[2.5rem] md:rounded-[3.5rem] border border-white shadow-sm hover:shadow-2xl transition-all duration-700 block">
                        
                        <div class="relative aspect-[4/5] rounded-[2rem] md:rounded-[2.8rem] overflow-hidden bg-slate-100 mb-6">
                            <img :src="product.images?.[0] ? '/storage/products/' + product.images[0].path : 'https://placehold.co/600x800'" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                            
                            <div class="absolute inset-0 bg-indigo-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                                <div class="bg-white p-4 rounded-full scale-50 group-hover:scale-100 transition-transform duration-500 shadow-2xl">
                                    <ShoppingBag class="w-6 h-6 text-slate-900" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-3">
                            <h3 class="text-xs md:text-sm font-black uppercase truncate text-slate-800 tracking-tight">{{ product.description }}</h3>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-sm md:text-2xl font-black text-indigo-600 font-mono tracking-tighter">R$ {{ product.sale_price }}</p>
                                <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Ver Mais</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="text-center py-32 bg-white rounded-[4rem] border-4 border-dashed border-slate-50">
                    <SearchX class="w-16 h-16 text-slate-200 mx-auto mb-6" />
                    <p class="text-slate-400 font-black uppercase tracking-[0.3em] text-sm italic">Nenhum resultado para os filtros aplicados</p>
                </div>
            </section>
        </main>

        <Transition enter-active-class="duration-700 ease-out" enter-from-class="opacity-0 translate-y-10" enter-to-class="opacity-100 translate-y-0">
            <div v-if="showTermsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/95 backdrop-blur-2xl">
                <div class="bg-white w-full max-w-xl rounded-[4rem] p-12 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-indigo-600"></div>
                    
                    <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mb-8">
                        <ShieldCheck class="w-10 h-10" />
                    </div>

                    <h2 class="text-3xl font-black text-slate-900 mb-6 uppercase italic tracking-tighter leading-none">Proteção de Dados & Auditoria</h2>
                    <p class="text-slate-500 text-sm mb-10 leading-relaxed font-medium">
                        Para sua segurança, registramos seu IP e atividades para fins de auditoria cibernética. 
                        Ao prosseguir, você concorda com nossos <a href="#" class="text-indigo-600 font-black underline decoration-2 underline-offset-4">termos de uso</a> e política de privacidade.
                    </p>

                    <label class="flex items-center gap-4 cursor-pointer mb-10 bg-slate-50 p-6 rounded-3xl border border-slate-100 hover:bg-indigo-50 transition-colors group">
                        <input type="checkbox" v-model="termsAccepted" class="h-6 w-6 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all" />
                        <span class="text-[11px] font-black text-slate-700 uppercase tracking-tight group-hover:text-indigo-600">Compreendo e aceito as condições de monitoramento.</span>
                    </label>

                    <button @click="acceptTerms" :disabled="!termsAccepted"
                        class="w-full py-6 rounded-2xl font-black uppercase tracking-[0.2em] text-xs transition-all shadow-xl"
                        :class="termsAccepted ? 'bg-slate-900 text-white hover:bg-indigo-600 hover:-translate-y-1' : 'bg-slate-100 text-slate-300 cursor-not-allowed'">
                        Confirmar Acesso
                    </button>
                </div>
            </div>
        </Transition>

    </StoreLayout>
</template>

<style scoped>
/* Esconde scrollbar mas mantém funcionalidade */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

/* Suaviza o sticky ao rolar */
.sticky {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>