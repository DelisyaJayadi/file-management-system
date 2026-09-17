import api from './api';
import type { Department } from './departmentService'; 

export interface User {
    id: number;
    name: string;
    email: string;
}

export interface FileItem {
    id: number;
    title: string;
    filename: string;
    department_id: number;
    folder_id: number | null;
    user_id: number;
    created_at?: string;
    updated_at?: string;
}

export interface Folder {
    id: number;
    name: string;
    department_id: number;
    parent_id: number | null;
    user_id: number;
    created_at?: string;
    updated_at?: string;
    department?: Department;
    user?: User;
    children?: Folder[];
    files?: FileItem[];
}

export default {
    async getFolders(params?: { department_id?: number; parent_id?: number | null }): Promise<Folder[]> {
        const response = await api.get('/api/folders', { params });
        return response.data.data;
    },

    async createFolder(data: { name: string; department_id: number; parent_id?: number | null }): Promise<{ message: string; data: Folder }> {
        const response = await api.post('/api/folders', data);
        return response.data;
    },

    async getFolderDetail(id: number): Promise<Folder> {
        const response = await api.get(`/api/folders/${id}`);
        return response.data.data;
    },

    async deleteFolder(id: number): Promise<{ message: string }> {
        const response = await api.delete(`/api/folders/${id}`);
        return response.data;
    }
};