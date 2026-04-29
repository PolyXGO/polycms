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
                // Do not logout if it's a 2FA requirement (handled by controller/frontend)
                if (axios.isAxiosError(error) && error.response?.status === 403 && error.response?.data?.two_factor_required) {
                    throw error;
                }
                this.logout();
                throw error;
            }
        },
        async verify2FA(payload: { user_id: number; one_time_password: string }) {
            try {
                const response = await axios.post('/api/v1/auth/google2fa/verify', payload);
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
        async logout(hard: boolean = true) {
            try {
                // Only call logout API if we were actually authenticated
                if (this.token && this.isAuthenticated) {
                    await axios.post('/api/v1/auth/logout').catch(() => {});
                }
            } finally {
                this.token = null;
                this.user = null;
                this.isAuthenticated = false;
                localStorage.removeItem('auth_token');
                delete axios.defaults.headers.common['Authorization'];

                if (hard) {
                    // Also clear web session by submitting native form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/logout';

                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                    if (csrfTokenMeta) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_token';
                        input.value = csrfTokenMeta.getAttribute('content') || '';
                        form.appendChild(input);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        },
        async checkAuth() {
            if (this.token) {
                try {
                    axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                    const response = await axios.get('/api/v1/auth/me');
                    this.user = response.data.data;
                    this.isAuthenticated = true;
                } catch {
                    // Silently clear stale token — no API calls, no redirects
                    this.token = null;
                    this.user = null;
                    this.isAuthenticated = false;
                    localStorage.removeItem('auth_token');
                    delete axios.defaults.headers.common['Authorization'];
                }
            } else {
                this.isAuthenticated = false;
                this.user = null;
            }
        },
    },
});
