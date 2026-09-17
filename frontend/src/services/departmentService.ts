import api from './api';

export interface Department {
    id: number;
    name: string;
    description?: string;
}

export default {
    async getDepartments() {
        const response = await api.get('/api/departments');
        return response.data;
    },
    async createDepartment(data: { name: string; description?: string }) {
        const response = await api.post('/api/departments', data);
        return response.data;
    },
    async updateDepartment(id: number, data: { name: string; description?: string }) {
        const response = await api.put(`/api/departments/${id}`, data);
        return response.data;
    },
    async deleteDepartment(id: number) {
        const response = await api.delete(`/api/departments/${id}`);
        return response.data;
    }
};