<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import departmentService, { type Department } from '@/services/departmentService';

const authStore = useAuthStore();
const departments = ref<Department[]>([]);
const loading = ref(true);
const errorMessage = ref('');

// Form state untuk tambah department baru
const newDepartmentName = ref('');
const newDepartmentDesc = ref('');
const isSubmitting = ref(false);

const fetchDepartments = async () => {
    loading.value = true;
    try {
        const data = await departmentService.getDepartments();
        departments.value = data;
    } catch (error) {
        errorMessage.value = 'Gagal memuat data department.';
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const handleCreateDepartment = async () => {
    if (!newDepartmentName.value.trim()) return;

    isSubmitting.value = true;
    try {
        await departmentService.createDepartment({
            name: newDepartmentName.value,
            description: newDepartmentDesc.value,
        });
        newDepartmentName.value = '';
        newDepartmentDesc.value = '';
        await fetchDepartments(); // Refresh list
    } catch (error) {
        console.error('Gagal membuat department:', error);
        alert('Gagal menambah department.');
    } finally {
        isSubmitting.value = false;
    }
};

const handleDeleteDepartment = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus department ini?')) return;

    try {
        await departmentService.deleteDepartment(id);
        await fetchDepartments(); // Refresh list
    } catch (error) {
        console.error('Gagal menghapus department:', error);
        alert('Gagal menghapus department.');
    }
};

onMounted(() => {
    fetchDepartments();
});
</script>

<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Department</h1>

            <!-- Form Tambah Department (Hanya untuk Admin) -->
            <div v-if="authStore.user" class="bg-white p-6 shadow-sm rounded-lg mb-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Tambah Department Baru</h2>
                <form @submit.prevent="handleCreateDepartment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Department</label>
                        <input v-model="newDepartmentName" type="text" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
                            placeholder="Contoh: Keuangan, IT, HRD" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
                        <textarea v-model="newDepartmentDesc"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
                            placeholder="Keterangan singkat department"></textarea>
                    </div>
                    <button type="submit" :disabled="isSubmitting"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 disabled:opacity-50">
                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan Department' }}
                    </button>
                </form>
            </div>

            <!-- List Department -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Daftar Department</h2>

                <div v-if="loading" class="text-gray-500">Memuat data department...</div>
                <div v-else-if="errorMessage" class="text-red-500">{{ errorMessage }}</div>
                <div v-else-if="departments.length === 0" class="text-gray-500 border-2 border-dashed border-gray-200 rounded-lg p-6 text-center">
                    Belum ada data department.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="dept in departments" :key="dept.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ dept.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ dept.description || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="handleDeleteDepartment(dept.id)"
                                        class="text-red-600 hover:text-red-900 ml-4">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>