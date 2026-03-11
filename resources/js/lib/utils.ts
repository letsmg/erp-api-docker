import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}


export const fillFormData = (form: any) => {
    if (!form) return;

    const fakeData: Record<string, any> = {
        name: () => "Usuário de Teste " + Math.floor(Math.random() * 100),
        company_name: () => "Empresa Teste " + Math.random().toString(36).substring(7).toUpperCase(),
        email: () => `teste_${Math.random().toString(36).substring(5)}@zenite.com`,        
        cnpj: () => {
            const suffix = Math.floor(Math.random() * 9000) + 1000; // Gera 4 dígitos aleatórios
            return `00.000.000/0001-${suffix.toString().substring(0, 2)}`;
        },
        state_registration: () => "ISENTO",
        zip_code: () => "01001-000",
        address: () => "Rua de Teste, " + Math.floor(Math.random() * 999),
        neighborhood: () => "Bairro Industrial",
        city: () => "São Paulo",
        contact_name_1: () => "Contato Principal",
        phone_1: () => "(11) 98888-7777",
        password: () => "Mudar@123",
        password_confirmation: () => "Mudar@123",
        access_level: () => 0,
        is_active: () => true,
    };

    Object.keys(form.data()).forEach((key) => {
        if (fakeData[key]) {
            form[key] = fakeData[key]();
        } else if (typeof form[key] === 'string' && !form[key]) {
            form[key] = "Teste " + key.charAt(0).toUpperCase() + key.slice(1);
        }
    });
};


export const clearFormData = (form: any) => {
    if (!form) return;

    Object.keys(form.data()).forEach((key) => {
        const value = form[key];
        if (typeof value === 'string') form[key] = '';
        else if (typeof value === 'number') form[key] = 0;
        else if (typeof value === 'boolean') form[key] = true; // ou false, dependendo do seu padrão
        else if (Array.isArray(value)) form[key] = [];
    });
    
    // Limpa também os erros de validação que o Inertia possa ter guardado
    form.clearErrors();
};