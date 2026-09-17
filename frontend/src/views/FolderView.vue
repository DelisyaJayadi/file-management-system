<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import folderService, { type Folder } from '../services/folderService';
import departmentService, { type Department } from '../services/departmentService';
import fileService, { type FileItem } from '../services/fileService';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.role === 'administrator');

const folders = ref<Folder[]>([]);
const files = ref<FileItem[]>([]);
const departments = ref<Department[]>([]);
const selectedDepartmentId = ref<number | null>(null);
const currentParentId = ref<number | null>(null);
const currentFolderName = ref<string>('Root Folder');

const newFolderName = ref('');
const loading = ref(false);
const errorMessage = ref('');

// State untuk Upload File
const showUploadModal = ref(false);
const fileTitle = ref('');
const selectedFile = ref<File | null>(null);

// State untuk Edit File
const showEditModal = ref(false);
const editingFile = ref<FileItem | null>(null);
const editTitle = ref('');

// Ambil daftar departemen
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

// Ambil daftar folder dan file berdasarkan direktori aktif & departemen
const fetchData = async () => {
    loading.value = true;
    try {
        const params: { department_id?: number; parent_id?: number | null } = {
            parent_id: currentParentId.value
        };
        if (selectedDepartmentId.value) {
            params.department_id = selectedDepartmentId.value;
        }

        // Ambil folder dan semua file secara paralel atau berurutan
        folders.value = await folderService.getFolders(params);

        // Ambil file (filter berdasarkan folder aktif jika ada)
        const fileRes = await fileService.getFiles();
        // Filter file di sisi frontend sesuai folder_id & department_id saat ini
        files.value = fileRes.data.filter(f =>
            f.folder_id === currentParentId.value &&
            (!selectedDepartmentId.value || f.department_id === selectedDepartmentId.value)
        );
    } catch {
        errorMessage.value = 'Gagal memuat data direktori';
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
        await fetchData();
    } catch (error: unknown) {
        const err = error as { response?: { data?: { message?: string } } };
        errorMessage.value = err.response?.data?.message || 'Gagal membuat folder';
    }
};

// Hapus folder
const handleDeleteFolder = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus folder ini?')) return;
    try {
        await folderService.deleteFolder(id);
        await fetchData();
    } catch {
        errorMessage.value = 'Gagal menghapus folder';
    }
};

// Handler Upload File
const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        selectedFile.value = target.files[0];
    }
};

const handleUploadFile = async () => {
    if (!fileTitle.value.trim() || !selectedFile.value || !selectedDepartmentId.value) {
        errorMessage.value = 'Judul dan file wajib diisi!';
        return;
    }

    const formData = new FormData();
    formData.append('title', fileTitle.value);
    formData.append('file', selectedFile.value);
    formData.append('department_id', selectedDepartmentId.value.toString());
    if (currentParentId.value !== null) {
        formData.append('folder_id', currentParentId.value.toString());
    }

    try {
        await fileService.uploadFile(formData);
        fileTitle.value = '';
        selectedFile.value = null;
        showUploadModal.value = false;
        await fetchData();
    } catch (error: unknown) {
        const err = error as { response?: { data?: { message?: string } } };
        errorMessage.value = err.response?.data?.message || 'Gagal mengunggah file';
    }
};

// Download File
const handleDownloadFile = async (file: FileItem) => {
    try {
        await fileService.downloadFile(file.id, file.name);
    } catch {
        errorMessage.value = 'Gagal mengunduh file';
    }
};

// Edit File Modal Handler
const openEditFileModal = (file: FileItem) => {
    editingFile.value = file;
    editTitle.value = file.title;
    showEditModal.value = true;
};

const handleUpdateFile = async () => {
    if (!editingFile.value || !editTitle.value.trim()) return;
    try {
        await fileService.updateFile(editingFile.value.id, {
            title: editTitle.value,
            department_id: editingFile.value.department_id
        });
        showEditModal.value = false;
        editingFile.value = null;
        await fetchData();
    } catch {
        errorMessage.value = 'Gagal memperbarui informasi file';
    }
};

// Hapus File
const handleDeleteFile = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus file ini?')) return;
    try {
        await fileService.deleteFile(id);
        await fetchData();
    } catch {
        errorMessage.value = 'Gagal menghapus file';
    }
};

// Navigasi folder
const openFolder = (folder: Folder) => {
    currentParentId.value = folder.id;
    currentFolderName.value = folder.name;
    fetchData();
};

const goBack = () => {
    currentParentId.value = null;
    currentFolderName.value = 'Root Folder';
    fetchData();
};

onMounted(async () => {
    console.log('Is Admin:', isAdmin.value, authStore.user)
    await fetchDepartments();
    await fetchData();
});
</script>

<template>
    <div class="p-6 max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Manajemen File & Folder</h1>

        <!-- Notifikasi Error -->
        <div v-if="errorMessage" class="bg-red-100 text-red-700 p-3 rounded mb-4 flex justify-between items-center">
            <span>{{ errorMessage }}</span>
            <button @click="errorMessage = ''" class="font-bold">&times;</button>
        </div>

        <!-- Filter Department -->
        <div class="mb-4 flex gap-4 items-center">
            <label class="font-semibold">Departemen:</label>
            <select v-model="selectedDepartmentId" @change="fetchData" class="border p-2 rounded">
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                    {{ dept.name }}
                </option>
            </select>
        </div>

        <!-- Form Create Folder & Upload File (Hanya untuk Administrator) -->
        <!-- Form Create Folder & Upload File (Hanya untuk Administrator) -->
        <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Buat Folder -->
            <div class="bg-white p-4 rounded shadow flex gap-2 items-center">
                <input v-model="newFolderName" type="text" placeholder="Nama folder baru..."
                    class="border p-2 rounded flex-1" />
                <button @click="handleCreateFolder"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 whitespace-nowrap">
                    Buat Folder
                </button>
            </div>

            <!-- Upload File Trigger -->
            <div class="bg-white p-4 rounded shadow flex justify-between items-center">
                <span class="font-medium text-gray-700">Unggah File ke Direktori Ini</span>
                <button @click="showUploadModal = true"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    + Upload File
                </button>
            </div>
        </div>

        <!-- Breadcrumb / Navigasi Lokasi -->
        <div class="flex items-center gap-2 mb-4">
            <button v-if="currentParentId !== null" @click="goBack" class="text-blue-600 hover:underline">
                &larr; Kembali ke Root
            </button>
            <span class="text-gray-600 font-medium">Lokasi: / {{ currentFolderName }}</span>
        </div>

        <!-- Tabel Daftar Folder -->
        <div class="bg-white rounded shadow mb-6 overflow-hidden">
            <div class="bg-gray-100 p-3 font-semibold border-b">Folder</div>
            <table class="w-full text-left border-collapse">
                <tbody>
                    <tr v-if="loading">
                        <td colspan="3" class="p-4 text-center text-gray-500">Memuat data...</td>
                    </tr>
                    <tr v-else-if="folders.length === 0">
                        <td colspan="3" class="p-4 text-center text-gray-500">Tidak ada folder di direktori ini.</td>
                    </tr>
                    <tr v-for="folder in folders" :key="folder.id" class="border-b hover:bg-gray-50">
                        <td class="p-3 cursor-pointer text-blue-600 hover:underline" @click="openFolder(folder)">
                            📁 {{ folder.name }}
                        </td>
                        <td class="p-3 text-gray-600">Dibuat oleh: {{ folder.user?.name || '-' }}</td>
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

        <!-- Tabel Daftar File -->
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="bg-gray-100 p-3 font-semibold border-b">File</div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b text-sm text-gray-600">
                        <th class="p-3">Judul / Nama File</th>
                        <th class="p-3">Diupload Oleh</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="files.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">Tidak ada file di direktori ini.</td>
                    </tr>
                    <tr v-for="file in files" :key="file.id" class="border-b hover:bg-gray-50">
                        <td class="p-3">
                            <div class="font-semibold text-gray-800">📄 {{ file.title }}</div>
                            <div class="text-xs text-gray-500">{{ file.name }}</div>
                        </td>
                        <td class="p-3 text-gray-600 text-sm">{{ file.user?.name || '-' }}</td>
                        <td class="p-3 text-gray-600 text-sm">{{ new Date(file.created_at).toLocaleDateString() }}</td>
                        <td class="p-3 text-right space-x-2">
                            <button @click="handleDownloadFile(file)"
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Download
                            </button>
                            <template v-if="isAdmin">
                                <button @click="openEditFileModal(file)"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">
                                    Edit
                                </button>
                                <button @click="handleDeleteFile(file.id)"
                                    class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    Hapus
                                </button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Upload File -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white p-6 rounded shadow-lg max-w-md w-full">
                <h2 class="text-lg font-bold mb-4">Unggah File Baru</h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Judul File</label>
                    <input v-model="fileTitle" type="text" class="border w-full p-2 rounded"
                        placeholder="Masukkan judul file..." />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Pilih Berkas</label>
                    <input type="file" @change="handleFileChange" class="border w-full p-2 rounded" />
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="showUploadModal = false"
                        class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                    <button @click="handleUploadFile"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload</button>
                </div>
            </div>
        </div>

        <!-- Modal Edit File -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white p-6 rounded shadow-lg max-w-md w-full">
                <h2 class="text-lg font-bold mb-4">Edit Informasi File</h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Judul File</label>
                    <input v-model="editTitle" type="text" class="border w-full p-2 rounded" />
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="showEditModal = false"
                        class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                    <button @click="handleUpdateFile"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</template>