import api from './api';

export interface FileItem {
    id: number;
    title: string;
    name: string;
    path: string;
    mime_type: string;
    size: number;
    folder_id: number | null;
    department_id: number;
    user_id: number;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
    };
    department?: {
        id: number;
        name: string;
    };
}

export default {
    async getFiles(): Promise<{ message: string; data: FileItem[] }> {
        const response = await api.get('/api/files');
        return response.data;
    },

    async uploadFile(formData: FormData): Promise<{ message: string; data: FileItem }> {
        // Sesuaikan endpoint menjadi /api/files sesuai route backend
        const response = await api.post('/api/files', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response.data;
    },

    async updateFile(id: number, data: { title: string; department_id?: number }): Promise<{ message: string; data: FileItem }> {
        const response = await api.put(`/api/files/${id}`, data);
        return response.data;
    },

    async downloadFile(id: number, filename: string): Promise<void> {
        const response = await api.get(`/api/files/${id}/download`, {
            responseType: 'blob', // Penting untuk unduh file biner
        });
        
        // Buat elemen anchor virtual untuk memicu download di browser
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    },

    async deleteFile(id: number): Promise<{ message: string }> {
        const response = await api.delete(`/api/files/${id}`);
        return response.data;
    }
};