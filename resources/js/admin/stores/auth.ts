import { defineStore } from 'pinia';
import axios from 'axios';
import router from '../router';
import type { User } from '../types';

interface AuthState {
    isAuthenticated: boolean;
    user: User | null;
    token: string | null;
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        isAuthenticated: false,
        user: null,
        token: localStorage.getItem('auth_token'),
    }),
    getters: {
        isAdmin: (state) => state.user?.roles.includes('admin') ?? false,
        isEditor: (state) => state.user?.roles.includes('editor') ?? false,
        isAuthor: (state) => state.user?.roles.includes('author') ?? false,
    },
    actions: {
        async login(credentials: Record<string, string>) {
            try {
                const response = await axios.post('/api/v1/auth/login', credentials);
                this.token = response.data.data.token;
                this.user = response.data.data.user;
                this.isAuthenticated = true;
                localStorage.setItem('auth_token', this.token!);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                router.push({ name: 'admin.dashboard' });
            } catch (error) {
                this.logout();
                throw error;
            }
        },
        async logout() {
            try {
                await axios.post('/api/v1/auth/logout');
            } finally {
                this.token = null;
                this.user = null;
                this.isAuthenticated = false;
                localStorage.removeItem('auth_token');
                delete axios.defaults.headers.common['Authorization'];
                router.push({ name: 'admin.login' });
            }
        },
        async checkAuth() {
            if (this.token) {
                try {
                    const response = await axios.get('/api/v1/auth/me');
                    this.user = response.data.data;
                    this.isAuthenticated = true;
                    axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                } catch (error) {
                    this.logout();
                }
            } else {
                this.isAuthenticated = false;
                this.user = null;
            }
        },
    },
});
