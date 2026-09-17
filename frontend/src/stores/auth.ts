import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/services/api';

interface User {
    id: number;
    name: string;
    email: string;
    role?: string;
    [key: string]: string | number | boolean | null | undefined; 
}

interface LoginCredentials {
    email: string;
    password: string;
}

export const useAuthStore = defineStore('auth', () => {
    // State
    const user = ref<User | null>(null);
    const token = ref<string | null>(localStorage.getItem('token') || null);
    const isAuthenticated = ref<boolean>(!!token.value);

    // Actions
    const setToken = (newToken: string) => {
        token.value = newToken;
        isAuthenticated.value = true;
        localStorage.setItem('token', newToken);
    };

    const clearAuth = () => {
        user.value = null;
        token.value = null;
        isAuthenticated.value = false;
        localStorage.removeItem('token');
    };

    // Fungsi Login
    const login = async (credentials: LoginCredentials) => {
        try {
            // Mengambil CSRF Cookie dari Laravel Sanctum terlebih dahulu
            await api.get('/sanctum/csrf-cookie');

            // Request login ke backend API Laravel
            const response = await api.post('/api/login', credentials);

            // Simpan token dan data user
            setToken(response.data.access_token);
            user.value = response.data.user;

            return { success: true };
        } catch (error: unknown) {
            let errorMessage = 'Login gagal, periksa kembali email dan password.';

            // Type narrowing yang aman untuk error handling Axios
            if (error && typeof error === 'object' && 'response' in error) {
                const err = error as { response?: { data?: { message?: string } } };
                if (err.response?.data?.message) {
                    errorMessage = err.response.data.message;
                }
            }

            return {
                success: false,
                message: errorMessage
            };
        }
    };

    // Fungsi Logout
    const logout = async () => {
        try {
            await api.post('/api/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            clearAuth();
        }
    };

    return {
        user,
        token,
        isAuthenticated,
        login,
        logout,
    };
});