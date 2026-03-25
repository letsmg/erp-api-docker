import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';

export function useStoreIndex(props) {
    const page = usePage();

    // --- ESTADO DOS FILTROS ---
    const search = ref(props.filters?.search || '');
    const minPrice = ref(props.filters?.min_price || '');
    const maxPrice = ref(props.filters?.max_price || '');
    const brand = ref(props.filters?.brand || '');

    // --- FUNÇÃO DE AUXÍLIO: NORMALIZAÇÃO ---
    // Remove acentos e converte para minúsculas para uma verificação precisa
    const getNormalizedLength = (text) => {
        return text
            .normalize('NFD')               // Decompõe caracteres acentuados (ex: 'á' -> 'a' + '´')
            .replace(/[\u0300-\u036f]/g, "") // Remove os acentos
            .trim()                          // Remove espaços extras
            .length;
    };

    // --- FUNÇÃO CENTRAL DE REQUISIÇÃO ---
    const filterProducts = () => {
        router.get(route('store.index'), {
            search: search.value,
            min_price: minPrice.value,
            max_price: maxPrice.value,
            brand: brand.value
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    };

    // 🔍 WATCH DA BUSCA (Trava de 3 caracteres normalizados)
    watch(search, debounce((value) => {
        const length = getNormalizedLength(value);
        // Dispara se estiver vazio ou se tiver 3 ou mais letras (mesmo com acento)
        if (length >= 3 || length === 0) {
            filterProducts();
        }
    }, 500));

    // 💰 WATCH DOS FILTROS FIXOS
    watch([minPrice, maxPrice, brand], () => {
        filterProducts();
    });

    // --- RESTANTE DA LÓGICA (MODAL, CARROSSEL, ETC) ---
    const showTermsModal = ref(false);
    const termsAccepted = ref(false);

    const acceptTerms = () => {
        if (!termsAccepted.value) return;
        router.post(route('store.terms.accept'), {}, {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.setItem('erp_terms_accepted', 'true');
                showTermsModal.value = false;
            }
        });
    };

    const scroll = (id, direction) => {
        const el = document.getElementById(id);
        if (!el) return;
        const isAtEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 10;
        if (direction === 'right' && isAtEnd) {
            el.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            const offset = direction === 'left' ? -el.offsetWidth : el.offsetWidth;
            el.scrollBy({ left: offset, behavior: 'smooth' });
        }
    };

    // Handler para remover o listener corretamente
    const handleKeyDown = (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'm') {
            e.preventDefault();
            showTermsModal.value = true;
        }
    };

    let timer = null;
    onMounted(() => {
        if (!localStorage.getItem('erp_terms_accepted')) {
            showTermsModal.value = true;
        }
        window.addEventListener('keydown', handleKeyDown);
        timer = setInterval(() => scroll('hero-carousel', 'right'), 7000);
    });

    onUnmounted(() => {
        if (timer) clearInterval(timer);
        window.removeEventListener('keydown', handleKeyDown); 
    });

    const seoData = computed(() => page.props.store_seo ?? {
        title: "Vitrine Premium | ERP Zenite",
        description: "Explore nossa seleção exclusiva de produtos de alta qualidade.",
        h1: "Catálogo de Produtos"
    });

    return {
        search, minPrice, maxPrice, brand,
        showTermsModal, termsAccepted, acceptTerms,
        scroll, seoData
    };
}