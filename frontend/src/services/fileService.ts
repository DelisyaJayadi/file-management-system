import api from './api';

export default {
    async getFolders() {
        const response = await api.get('/api/folders');
        return response.data;
    },
    async getFiles() {
        const response = await api.get('/api/files');
        return response.data;
    },
    async uploadFile(formData: FormData) {
        const response = await api.post('/api/files/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response.data;
    }
};