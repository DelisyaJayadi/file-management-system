<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import fileService from '@/services/fileService';

interface Folder {
    id: number | string;
    name: string;
}

const authStore = useAuthStore();
const router = useRouter();

const folders = ref<Folder[]>([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const data = await fileService.getFolders();
        folders.value = data;
    } catch (error) {
        console.error('Gagal memuat data folder:', error);
    } finally {
        loading.value = false;
    }
});

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
                        <router-link to="/departments"
                            class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            Kelola Department
                        </router-link>
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
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Folder Anda</h2>

                    <div v-if="loading" class="text-gray-500">Memuat data...</div>

                    <div v-else-if="folders.length === 0"
                        class="text-gray-500 border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                        Belum ada folder yang tersedia.
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <!-- Tampilkan list folder di sini -->
                        <li v-for="folder in folders" :key="folder.id" class="py-3 flex justify-between items-center">
                            <span class="text-gray-700 font-medium">{{ folder.name }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</template>