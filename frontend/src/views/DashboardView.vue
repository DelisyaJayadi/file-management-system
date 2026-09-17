<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import folderService, { type Folder } from '@/services/folderService';
import fileService, { type FileItem } from '@/services/fileService';
import departmentService, { type Department } from '@/services/departmentService';

const authStore = useAuthStore();
const router = useRouter();

const folders = ref<Folder[]>([]);
const files = ref<FileItem[]>([]);
const departments = ref<Department[]>([]);
const loading = ref(true);

const fetchDashboardData = async () => {
    try {
        // Ambil data secara paralel untuk efisiensi
        const [folderRes, fileRes, deptRes] = await Promise.all([
            folderService.getFolders(),
            fileService.getFiles(),
            departmentService.getDepartments()
        ]);

        folders.value = folderRes;
        files.value = fileRes.data;
        departments.value = deptRes;
    } catch (error) {
        console.error('Gagal memuat data dashboard:', error);
    } finally {
        loading.value = false;
    }
};

// Ambil 10 file terbaru berdasarkan tanggal dibuat
const recentFiles = computed(() => {
    return [...files.value]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        .slice(0, 10);
});

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};

onMounted(() => {
    fetchDashboardData();
});
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
                        <router-link to="/folders"
                            class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            Kelola Folder & File
                        </router-link>
                        <router-link v-if="authStore.user?.role === 'administrator'" to="/departments"
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
                
                <!-- Statistik Ringkasan -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                        <div class="text-sm font-medium text-gray-500">Total Folder</div>
                        <div class="text-2xl font-bold text-gray-800 mt-1">{{ folders.length }}</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                        <div class="text-sm font-medium text-gray-500">Total File</div>
                        <div class="text-2xl font-bold text-gray-800 mt-1">{{ files.length }}</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                        <div class="text-sm font-medium text-gray-500">Total Department</div>
                        <div class="text-2xl font-bold text-gray-800 mt-1">{{ departments.length }}</div>
                    </div>
                </div>

                <!-- 10 File Terbaru -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">10 File Terbaru</h2>

                    <div v-if="loading" class="text-gray-500">Memuat data...</div>

                    <div v-else-if="recentFiles.length === 0"
                        class="text-gray-500 border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                        Belum ada file yang tersedia.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b text-sm text-gray-600">
                                    <th class="p-3">Judul File</th>
                                    <th class="p-3">Nama Berkas</th>
                                    <th class="p-3">Diupload Oleh</th>
                                    <th class="p-3">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="file in recentFiles" :key="file.id" class="border-b hover:bg-gray-50 text-sm">
                                    <td class="p-3 font-semibold text-gray-800">📄 {{ file.title }}</td>
                                    <td class="p-3 text-gray-600">{{ file.name }}</td>
                                    <td class="p-3 text-gray-600">{{ file.user?.name || '-' }}</td>
                                    <td class="p-3 text-gray-600">{{ new Date(file.created_at).toLocaleDateString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>