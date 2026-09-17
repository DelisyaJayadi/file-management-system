<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navbar -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">File Management System</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span v-if="authStore.user" class="text-sm text-gray-700">
                            Halo, <span class="font-semibold">{{ authStore.user.name }}</span>
                        </span>
                        <button @click="handleLogout"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150">
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 p-6 flex flex-col items-center justify-center bg-white shadow-sm">
                    <h2 class="text-2xl font-semibold text-gray-700 mb-2">Selamat Datang di Dashboard</h2>
                    <p class="text-gray-500 text-center max-w-md">
                        Sistem manajemen file Anda berhasil terhubung dengan backend Laravel. Modul unggah file dan manajemen folder akan segera kita bangun di sini!
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>