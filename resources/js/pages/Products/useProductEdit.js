import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

export function useProductEdit(props) {
    const activeTab = ref('geral');
    const newImagePreviews = ref([]);
    const tagInput = ref('');

    /**
     * Formata data do banco (YYYY-MM-DD HH:mm:ss) para o input (YYYY-MM-DDTHH:mm)
     */
    const formatDate = (dateString) => {
        if (!dateString) return '';
        return dateString.replace(' ', 'T').substring(0, 16);
    };

    const form = useForm({
        _method: 'PUT', // Necessário para Laravel aceitar arquivos via Multipart Form Data

        // --- Dados Gerais ---
        description: props.product.description || '',
        supplier_id: props.product.supplier_id || '',
        barcode: props.product.barcode || '',
        brand: props.product.brand || '',
        model: props.product.model || '',
        collection: props.product.collection || '',
        size: props.product.size || '',
        gender: props.product.gender || 'Unissex',
        stock_quantity: props.product.stock_quantity || 0,

        // --- Financeiro ---
        cost_price: Number(props.product.cost_price) || 0,
        sale_price: Number(props.product.sale_price) || 0,
        promo_price: props.product.promo_price ? Number(props.product.promo_price) : null,
        promo_start_at: formatDate(props.product.promo_start_at),
        promo_end_at: formatDate(props.product.promo_end_at),

        // --- Logística ---
        weight: Number(props.product.weight) || 0,
        width: Number(props.product.width) || 0,
        height: Number(props.product.height) || 0,
        length: Number(props.product.length) || 0,
        free_shipping: Boolean(props.product.free_shipping),

        // --- SEO & Marketing ---
        google_tag_manager: props.product.seo?.google_tag_manager || '',
        ads: props.product.seo?.ads || '',
        canonical_url: props.product.seo?.canonical_url || '',
        meta_title: props.product.seo?.meta_title || props.product.seo_display?.meta_title || '',
        meta_description: props.product.seo?.meta_description || props.product.seo_display?.meta_description || '',
        
        // Converte string "tag1, tag2" para Array
        meta_keywords: props.product.seo?.meta_keywords 
            ? props.product.seo.meta_keywords.split(',').map(s => s.trim()).filter(s => s !== "") 
            : [],

        // Conteúdo On-Page
        h1: props.product.seo?.h1 || props.product.seo_display?.h1 || '',
        h2: props.product.seo?.h2 || '',
        text1: props.product.seo?.text1 || '',
        text2: props.product.seo?.text2 || '',
        schema_markup: props.product.seo?.schema_markup || '',

        // --- Gestão de Imagens ---
        existing_images: [...(props.product.images || [])],
        new_images: [],
        removed_images: [], 
    });

    // --- Lógica de Negócio ---

    const addTag = () => {
        const val = tagInput.value.trim();
        if (val && !form.meta_keywords.includes(val)) {
            form.meta_keywords.push(val);
            tagInput.value = ''; 
        }
    };

    const removeTag = (index) => {
        form.meta_keywords.splice(index, 1);
    };

    const profitData = computed(() => {
        const cost = parseFloat(form.cost_price) || 0;
        const sale = parseFloat(form.sale_price) || 0;
        const profit = sale - cost;
        const margin = cost > 0 ? (profit / cost) * 100 : 0;
        
        return {
            value: profit.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
            percentage: margin.toFixed(2)
        };
    });

    const handleImageUpload = (e) => {
        const files = Array.from(e.target.files);
        const currentTotal = form.existing_images.length + form.new_images.length;
        const remainingSlots = 6 - currentTotal;
        
        if (remainingSlots <= 0) {
            alert("Limite de 6 imagens atingido.");
            return;
        }

        files.slice(0, remainingSlots).forEach(file => {
            form.new_images.push(file);
            const reader = new FileReader();
            reader.onload = (e) => newImagePreviews.value.push(e.target.result);
            reader.readAsDataURL(file);
        });
    };

    const removeExistingImage = (index) => {
        const image = form.existing_images[index];
        form.removed_images.push(image.id); 
        form.existing_images.splice(index, 1);
    };

    const removeNewImage = (index) => {
        form.new_images.splice(index, 1);
        newImagePreviews.value.splice(index, 1);
    };

    const moveImage = (type, index, direction) => {
        const list = type === 'existing' ? form.existing_images : form.new_images;
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= list.length) return;

        const temp = list[index];
        list[index] = list[newIndex];
        list[newIndex] = temp;

        if (type === 'new') {
            const tempPreview = newImagePreviews.value[index];
            newImagePreviews.value[index] = newImagePreviews.value[newIndex];
            newImagePreviews.value[newIndex] = tempPreview;
        }
    };

    const submit = () => {
        form.transform((data) => ({
            ...data,
            // Converte array de tags de volta para string para o banco
            meta_keywords: Array.isArray(data.meta_keywords) 
                ? data.meta_keywords.join(', ') 
                : data.meta_keywords,
            // O backend usará a ordem deste array para atualizar a coluna 'order'
            existing_images: data.existing_images
        })).post(route('products.update', props.product.id), {
            preserveScroll: true,
            forceFormData: true, 
            onSuccess: () => {
                newImagePreviews.value = [];
                form.new_images = [];
                form.removed_images = [];
            },
        });
    };

    return {
        form, 
        activeTab, 
        newImagePreviews, 
        tagInput,
        addTag, 
        removeTag, 
        handleImageUpload, 
        removeExistingImage, 
        removeNewImage,
        moveImage,
        profitData, 
        submit
    };
}