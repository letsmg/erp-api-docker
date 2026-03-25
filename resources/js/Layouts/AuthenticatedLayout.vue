<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, Users, Package, LogOut, CheckCircle2, X, AlertTriangle,
    FileBarChart, ShoppingCart, Contact2, ChevronDown, Menu
} from 'lucide-vue-next';
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- Controle de UI ---
const isMobileMenuOpen = ref(false);
const showReportsMenu = ref(false);
const toggleMobileMenu = () => isMobileMenuOpen.value = !isMobileMenuOpen.value;

// --- Lógica de Notificações (Toast) ---
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const triggerToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    
    const duration = type === 'error' ? 6000 : 4000;
    setTimeout(() => { showToast.value = false; }, duration);
};

// Monitorar Mensagens de Sucesso (Flash)
watch(() => page.props.flash?.message, (newMessage) => {
    if (newMessage) triggerToast(newMessage, 'success');
}, { immediate: true });

// Monitorar Erros de Validação (Importante para o seu caso)
const errors = computed(() => page.props.errors);
watch(errors, (newErrors) => {
    const errorKeys = Object.keys(newErrors);
    if (errorKeys.length > 0) {
        // Pega a primeira mensagem de erro para exibir no Toast
        const firstErrorMessage = newErrors[errorKeys[0]];
        triggerToast(firstErrorMessage, 'error');
    }
}, { deep: true });

// --- Atalhos e Utilitários ---
const handleKeyDown = (e) => {
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'p') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('magic-fill'));
    }
};

onMounted(() => window.addEventListener('keydown', handleKeyDown));
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));

const isUrl = (url) => page.url === url || page.url.startsWith(url + '/');

// Fecha menu mobile ao navegar
watch(() => page.url, () => isMobileMenuOpen.value = false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex overflow-x-hidden font-sans text-slate-900">
        
        <Transition 
            enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false"
                 class="fixed inset-0 bg-slate-900/60 z-40 md:hidden backdrop-blur-sm"></div>
        </Transition>

        <aside :class="[
            'fixed inset-y-0 left-0 w-64 bg-slate-950 text-white flex flex-col z-50 transition-transform duration-300 ease-in-out md:translate-x-0',
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
        ]">
            <div class="p-6 border-b border-slate-900 flex justify-between items-center">
                <span class="font-black text-xl tracking-tighter">ERP<span class="text-indigo-500">PRO</span></span>
                <button @click="isMobileMenuOpen = false" class="md:hidden p-1 hover:bg-slate-900 rounded">
                    <X class="w-6 h-6"/>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto custom-scrollbar">
                <Link :href="route('dashboard')" 
                    :class="[isUrl('/dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-white']"
                    class="flex items-center p-3 rounded-xl transition-all duration-200 group">
                    <LayoutDashboard class="w-5 group-hover:scale-110 transition-transform"/>
                    <span class="ml-3 font-medium">Dashboard</span>
                </Link>

                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-6 mb-2 px-3">Comercial</p>
                <div class="menu-item opacity-40 cursor-not-allowed">
                    <Contact2 class="w-5"/> <span>Clientes</span>
                </div>
                <div class="menu-item opacity-40 cursor-not-allowed">
                    <ShoppingCart class="w-5"/> <span>Vendas</span>
                </div>

                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-6 mb-2 px-3">Logística</p>
                <Link :href="route('products.index')" 
                    :class="[isUrl('/products') ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-900 hover:text-white']"
                    class="menu-item transition-all">
                    <Package class="w-5"/> <span>Produtos</span>
                </Link>

                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-6 mb-2 px-3">Gestão</p>
                <Link :href="route('users.index')" 
                    :class="[isUrl('/users') ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-900 hover:text-white']"
                    class="menu-item">
                    <Users class="w-5"/> <span>Usuários</span>
                </Link>

                <div class="pt-2">
                    <button @click="showReportsMenu = !showReportsMenu"
                        class="flex items-center justify-between w-full p-3 rounded-xl text-slate-400 hover:bg-slate-900 hover:text-white transition-colors">
                        <div class="flex items-center gap-3">
                            <FileBarChart class="w-5"/>
                            <span class="font-medium">Relatórios</span>
                        </div>
                        <ChevronDown :class="{'rotate-180': showReportsMenu}" class="w-4 h-4 transition-transform duration-300"/>
                    </button>

                    <Transition
                        enter-active-class="transition duration-200 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-150 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                        <div v-if="showReportsMenu" class="ml-11 mt-1 space-y-1">
                            <Link :href="route('reports.products')" class="block p-2 text-sm text-slate-500 hover:text-white transition-colors">Produtos</Link>
                            <span class="block p-2 text-sm text-slate-700 cursor-not-allowed italic">Vendas</span>
                        </div>
                    </Transition>
                </div>
            </nav>

            <div class="p-4 border-t border-slate-900">
                <Link :href="route('logout')" method="post" as="button" 
                    class="flex items-center gap-3 w-full p-3 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all">
                    <LogOut class="w-5"/> <span class="font-medium">Sair do Sistema</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 md:ml-64 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="toggleMobileMenu" class="md:hidden p-2 text-slate-600">
                        <Menu class="w-6 h-6"/>
                    </button>
                    <h2 class="hidden md:block text-sm font-medium text-slate-400 uppercase tracking-widest">Painel de Controle</h2>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-slate-900">{{ user.name }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-tighter">Administrador</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-700 font-black">
                        {{ user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-8 flex-1">
                <slot />
            </main>
        </div>

        <Transition 
            enter-active-class="transform transition duration-300 ease-out" enter-from-class="translate-y-10 opacity-0 sm:translate-x-10" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showToast" class="fixed bottom-6 right-6 z-[100] w-full max-w-sm px-4 sm:px-0">
                <div :class="[
                    'p-4 rounded-2xl shadow-2xl border flex items-center gap-4 backdrop-blur-md',
                    toastType === 'success' ? 'bg-emerald-50/90 border-emerald-200 text-emerald-900' : 'bg-red-50/90 border-red-200 text-red-900'
                ]">
                    <div :class="['p-2 rounded-xl shrink-0', toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500']">
                        <CheckCircle2 v-if="toastType === 'success'" class="w-5 h-5 text-white" />
                        <AlertTriangle v-else class="w-5 h-5 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-0.5">
                            {{ toastType === 'success' ? 'Sucesso' : 'Erro de Validação' }}
                        </p>
                        <p class="text-sm font-bold leading-tight">{{ toastMessage }}</p>
                    </div>
                    <button @click="showToast = false" class="p-1 hover:bg-black/5 rounded-lg transition-colors">
                        <X class="w-4 h-4 opacity-50" />
                    </button>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    font-weight: 500;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e293b;
    border-radius: 10px;
}
</style>