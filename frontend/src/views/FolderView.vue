<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import folderService, { type Folder } from '../services/folderService';
import departmentService, { type Department } from '../services/departmentService';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.role === 'administrator');

const folders = ref<Folder[]>([]);
const departments = ref<Department[]>([]);
const selectedDepartmentId = ref<number | null>(null);
const currentParentId = ref<number | null>(null);
const currentFolderName = ref<string>('Root Folder');

const newFolderName = ref('');
const loading = ref(false);
const errorMessage = ref('');

// Ambil daftar departemen untuk filter/pembuatan folder
const fetchDepartments = async () => {
    try {
        departments.value = await departmentService.getDepartments();
        if (departments.value && departments.value.length > 0 && !selectedDepartmentId.value) {
            selectedDepartmentId.value = departments.value[0]?.id ?? null;
        }
    } catch {
        errorMessage.value = 'Gagal memuat departemen';
    }
};

// Ambil daftar folder berdasarkan departemen dan parent_id saat ini
const fetchFolders = async () => {
    loading.value = true;
    try {
        const params: { department_id?: number; parent_id?: number | null } = {
            parent_id: currentParentId.value
        };
        if (selectedDepartmentId.value) {
            params.department_id = selectedDepartmentId.value;
        }
        folders.value = await folderService.getFolders(params);
    } catch {
        errorMessage.value = 'Gagal memuat daftar folder';
    } finally {
        loading.value = false;
    }
};

// Buat folder baru
const handleCreateFolder = async () => {
    if (!newFolderName.value.trim() || !selectedDepartmentId.value) return;
    try {
        await folderService.createFolder({
            name: newFolderName.value,
            department_id: selectedDepartmentId.value,
            parent_id: currentParentId.value
        });
        newFolderName.value = '';
        await fetchFolders();
    } catch (error: unknown) {
        // Variabel error digunakan di sini, jadi tidak akan warning
        const err = error as { response?: { data?: { message?: string } } };
        errorMessage.value = err.response?.data?.message || 'Gagal membuat folder';
    }
};

// Hapus folder
const handleDeleteFolder = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus folder ini?')) return;
    try {
        await folderService.deleteFolder(id);
        await fetchFolders();
    } catch {
        errorMessage.value = 'Gagal menghapus folder';
    }
};

// Navigasi masuk ke dalam sub-folder
const openFolder = (folder: Folder) => {
    currentParentId.value = folder.id;
    currentFolderName.value = folder.name;
    fetchFolders();
};

// Kembali ke level folder sebelumnya (atau root)
const goBack = () => {
    currentParentId.value = null;
    currentFolderName.value = 'Root Folder';
    fetchFolders();
};

onMounted(async () => {
    await fetchDepartments();
    await fetchFolders();
});
</script>

<template>
    <div class="p-6 max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Manajemen Folder</h1>

        <!-- Notifikasi Error -->
        <div v-if="errorMessage" class="bg-red-100 text-red-700 p-3 rounded mb-4">
            {{ errorMessage }}
        </div>

        <!-- Filter Department -->
        <div class="mb-4 flex gap-4 items-center">
            <label class="font-semibold">Departemen:</label>
            <select v-model="selectedDepartmentId" @change="fetchFolders" class="border p-2 rounded">
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                    {{ dept.name }}
                </option>
            </select>
        </div>

        <!-- Form Create Folder (Hanya untuk Administrator) -->
        <div v-if="isAdmin" class="bg-white p-4 rounded shadow mb-6 flex gap-2">
            <input v-model="newFolderName" type="text" placeholder="Nama folder baru..."
                class="border p-2 rounded flex-1" />
            <button @click="handleCreateFolder" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Buat Folder
            </button>
        </div>

        <!-- Breadcrumb / Navigasi Lokasi -->
        <div class="flex items-center gap-2 mb-4">
            <button v-if="currentParentId !== null" @click="goBack" class="text-blue-600 hover:underline">
                &larr; Kembali ke Root
            </button>
            <span class="text-gray-600 font-medium">Lokasi: / {{ currentFolderName }}</span>
        </div>

        <!-- Tabel Daftar Folder -->
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="p-3">Nama Folder</th>
                        <th class="p-3">Dibuat Oleh</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="3" class="p-4 text-center text-gray-500">Memuat data...</td>
                    </tr>
                    <tr v-else-if="folders.length === 0">
                        <td colspan="3" class="p-4 text-center text-gray-500">Tidak ada folder di dalam direktori ini.
                        </td>
                    </tr>
                    <tr v-for="folder in folders" :key="folder.id" class="border-b hover:bg-gray-50">
                        <td class="p-3 cursor-pointer text-blue-600 hover:underline" @click="openFolder(folder)">
                            📁 {{ folder.name }}
                        </td>
                        <td class="p-3 text-gray-600">{{ folder.user?.name || '-' }}</td>
                        <td class="p-3 text-right">
                            <button @click="openFolder(folder)"
                                class="bg-gray-200 px-3 py-1 rounded text-sm mr-2 hover:bg-gray-300">
                                Buka
                            </button>
                            <button v-if="isAdmin" @click="handleDeleteFolder(folder.id)"
                                class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>