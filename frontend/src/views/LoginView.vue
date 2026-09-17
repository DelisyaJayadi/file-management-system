<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

const handleLogin = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    const result = await authStore.login({
        email: email.value,
        password: password.value,
    });

    isLoading.value = false;

    if (result.success) {
        router.push('/dashboard');
    } else {
        errorMessage.value = result.message || 'Terjadi kesalahan saat login.';
    }
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8 bg-white p-8 rounded-xl shadow-md">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    File Management System
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Silakan masuk ke akun Anda
                </p>
            </div>

            <!-- Pesan Error -->
            <div v-if="errorMessage" class="bg-red-50 border-l-4 border-red-400 p-4 text-sm text-red-700" role="alert">
                <p>{{ errorMessage }}</p>
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email-address" class="sr-only">Alamat Email</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required
                            v-model="email"
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Alamat Email" />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            v-model="password"
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Password" />
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="isLoading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        <span v-if="isLoading">Memproses...</span>
                        <span v-else>Masuk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>