import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global interceptor for demo restriction responses across AJAX calls
window.axios.interceptors.response.use(
    (response) => {
        if (response.data && response.data.is_demo_restriction) {
            window.dispatchEvent(new CustomEvent('polycms:demo_restriction', { detail: response.data }));
        }
        return response;
    },
    (error) => {
        const data = error.response?.data;
        if (data && data.is_demo_restriction) {
            window.dispatchEvent(new CustomEvent('polycms:demo_restriction', { detail: data }));
        }
        return Promise.reject(error);
    }
);
