<template>
    <div class="reviews-page">
        <header class="reviews-page__header">
            <h1 class="reviews-page__title">{{ $t('Product Reviews') }}</h1>
            <div class="reviews-page__filters">
                <select v-model="filter.status" @change="loadReviews" class="filter-select">
                    <option value="">{{ $t('All Status') }}</option>
                    <option value="pending">{{ $t('Pending') }}</option>
                    <option value="approved">{{ $t('Approved') }}</option>
                    <option value="rejected">{{ $t('Rejected') }}</option>
                </select>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="reviews-stats">
            <div class="stat-card stat-card--pending">
                <span class="stat-card__value">{{ stats.pending }}</span>
                <span class="stat-card__label">{{ $t('Pending') }}</span>
            </div>
            <div class="stat-card stat-card--approved">
                <span class="stat-card__value">{{ stats.approved }}</span>
                <span class="stat-card__label">{{ $t('Approved') }}</span>
            </div>
            <div class="stat-card stat-card--rejected">
                <span class="stat-card__value">{{ stats.rejected }}</span>
                <span class="stat-card__label">{{ $t('Rejected') }}</span>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="reviews-list">
            <div v-if="loading" class="reviews-list__loading">
                <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div v-else-if="reviews.length === 0" class="reviews-list__empty">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-gray-500 dark:text-gray-400">{{ $t('No reviews found') }}</p>
            </div>

            <div v-else class="reviews-table-wrapper">
                <table class="reviews-table">
                    <thead>
                        <tr>
                            <th>{{ $t('Product') }}</th>
                            <th>{{ $t('Customer') }}</th>
                            <th>{{ $t('Rating') }}</th>
                            <th>{{ $t('Review') }}</th>
                            <th>{{ $t('Status') }}</th>
                            <th>{{ $t('Date') }}</th>
                            <th>{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="review in reviews" :key="review.id">
                            <td>
                                <a :href="`/admin/products/${review.product?.id}/edit`" class="review-product-name">
                                    {{ review.product?.name || 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <div class="review-customer">
                                    <span class="review-customer__name">{{ review.user?.name || 'N/A' }}</span>
                                    <span class="review-customer__email">{{ review.user?.email }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="review-rating">
                                    <span v-for="star in 5" :key="star" class="star" :class="{ 'star--filled': star <= review.rating }">★</span>
                                </div>
                            </td>
                            <td>
                                <div class="review-content">
                                    <strong v-if="review.title" class="review-content__title">{{ review.title }}</strong>
                                    <p class="review-content__text">{{ truncate(review.content, 80) }}</p>
                                    <span v-if="review.verified_purchase" class="review-verified">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $t('Verified') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge" :class="`status-badge--${review.status}`">
                                    {{ review.status }}
                                </span>
                            </td>
                            <td class="review-date">{{ formatDate(review.created_at) }}</td>
                            <td>
                                <div class="review-actions">
                                    <button v-if="review.status !== 'approved'" @click="approveReview(review)" class="review-action review-action--approve" :title="$t('Approve')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <button v-if="review.status !== 'rejected'" @click="rejectReview(review)" class="review-action review-action--reject" :title="$t('Reject')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.lastPage > 1" class="reviews-pagination">
                <button @click="goToPage(pagination.currentPage - 1)" :disabled="pagination.currentPage <= 1" class="pagination-btn">←</button>
                <span class="pagination-info">{{ pagination.currentPage }} / {{ pagination.lastPage }}</span>
                <button @click="goToPage(pagination.currentPage + 1)" :disabled="pagination.currentPage >= pagination.lastPage" class="pagination-btn">→</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';

const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || ((s: string) => s);

interface Review {
    id: number;
    product?: { id: number; name: string; slug: string };
    user?: { id: number; name: string; email: string };
    rating: number;
    title?: string;
    content?: string;
    status: string;
    verified_purchase: boolean;
    created_at: string;
}

const reviews = ref<Review[]>([]);
const loading = ref(false);
const filter = reactive({ status: '' });
const stats = reactive({ pending: 0, approved: 0, rejected: 0 });
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });

function truncate(text: string | undefined, max: number): string {
    if (!text) return '';
    return text.length > max ? text.substring(0, max) + '…' : text;
}

function formatDate(dateStr: string): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

async function loadReviews(page = 1) {
    loading.value = true;
    try {
        const params: any = { page, per_page: 15 };
        if (filter.status) params.status = filter.status;

        const { data } = await axios.get('/api/v1/reviews', { params });
        reviews.value = data.data || [];
        pagination.currentPage = data.current_page || 1;
        pagination.lastPage = data.last_page || 1;
        pagination.total = data.total || 0;
    } catch (e) {
        console.error('Failed to load reviews:', e);
    } finally {
        loading.value = false;
    }
}

async function loadStats() {
    try {
        const [pending, approved, rejected] = await Promise.all([
            axios.get('/api/v1/reviews', { params: { status: 'pending', per_page: 1 } }),
            axios.get('/api/v1/reviews', { params: { status: 'approved', per_page: 1 } }),
            axios.get('/api/v1/reviews', { params: { status: 'rejected', per_page: 1 } }),
        ]);
        stats.pending = pending.data.total || 0;
        stats.approved = approved.data.total || 0;
        stats.rejected = rejected.data.total || 0;
    } catch (e) {
        // silent
    }
}

async function approveReview(review: Review) {
    try {
        await axios.post(`/api/v1/reviews/${review.id}/approve`);
        review.status = 'approved';
        loadStats();
    } catch (e) {
        console.error('Failed to approve review:', e);
    }
}

async function rejectReview(review: Review) {
    try {
        await axios.post(`/api/v1/reviews/${review.id}/reject`);
        review.status = 'rejected';
        loadStats();
    } catch (e) {
        console.error('Failed to reject review:', e);
    }
}

function goToPage(page: number) {
    if (page < 1 || page > pagination.lastPage) return;
    loadReviews(page);
}

onMounted(() => {
    loadReviews();
    loadStats();
});
</script>

<style scoped>
.reviews-page { display: flex; flex-direction: column; gap: 1.5rem; }
.reviews-page__header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.reviews-page__title { font-size: 1.75rem; font-weight: 700; color: #0f172a; }
:root.dark .reviews-page__title { color: #f1f5f9; }

.filter-select { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #fff; color: #0f172a; font-size: 0.875rem; }
:root.dark .filter-select { background: #1e293b; color: #f1f5f9; border-color: #334155; }

.reviews-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.stat-card { padding: 1rem 1.25rem; border-radius: 0.75rem; background: #fff; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 0.25rem; }
:root.dark .stat-card { background: #1e293b; border-color: #334155; }
.stat-card__value { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
:root.dark .stat-card__value { color: #f1f5f9; }
.stat-card__label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-card--pending .stat-card__value { color: #f59e0b; }
.stat-card--approved .stat-card__value { color: #10b981; }
.stat-card--rejected .stat-card__value { color: #ef4444; }

.reviews-list { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; }
:root.dark .reviews-list { background: #1e293b; border-color: #334155; }
.reviews-list__loading, .reviews-list__empty { padding: 3rem; text-align: center; }
.reviews-table-wrapper { overflow-x: auto; }
.reviews-table { width: 100%; text-align: left; border-collapse: collapse; }
.reviews-table th { padding: 0.75rem 1rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-bottom: 1px solid #e2e8f0; }
:root.dark .reviews-table th { color: #94a3b8; border-color: #334155; }
.reviews-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.875rem; color: #0f172a; }
:root.dark .reviews-table td { border-color: #1e293b; color: #e2e8f0; }
.reviews-table tr:hover td { background: #f8fafc; }
:root.dark .reviews-table tr:hover td { background: #0f172a; }

.review-product-name { color: #4f46e5; font-weight: 500; text-decoration: none; }
.review-product-name:hover { text-decoration: underline; }
.review-customer { display: flex; flex-direction: column; }
.review-customer__name { font-weight: 500; }
.review-customer__email { font-size: 0.75rem; color: #94a3b8; }
.review-rating { display: flex; gap: 1px; }
.star { color: #d1d5db; font-size: 0.875rem; }
.star--filled { color: #f59e0b; }
.review-content { max-width: 250px; }
.review-content__title { display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.125rem; }
.review-content__text { color: #64748b; font-size: 0.8125rem; line-height: 1.4; }
.review-verified { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.6875rem; color: #10b981; margin-top: 0.25rem; }
.review-date { font-size: 0.8125rem; color: #64748b; white-space: nowrap; }

.status-badge { padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
.status-badge--pending { background: #fef3c7; color: #92400e; }
.status-badge--approved { background: #d1fae5; color: #065f46; }
.status-badge--rejected { background: #fee2e2; color: #991b1b; }
:root.dark .status-badge--pending { background: #78350f; color: #fef3c7; }
:root.dark .status-badge--approved { background: #064e3b; color: #d1fae5; }
:root.dark .status-badge--rejected { background: #7f1d1d; color: #fecaca; }

.review-actions { display: flex; gap: 0.375rem; }
.review-action { padding: 0.375rem; border-radius: 0.375rem; border: 1px solid transparent; cursor: pointer; transition: all 0.15s; background: none; }
.review-action--approve { color: #10b981; }
.review-action--approve:hover { background: #d1fae5; border-color: #a7f3d0; }
.review-action--reject { color: #ef4444; }
.review-action--reject:hover { background: #fee2e2; border-color: #fca5a5; }

.reviews-pagination { display: flex; align-items: center; justify-content: center; gap: 0.75rem; padding: 1rem; border-top: 1px solid #e2e8f0; }
:root.dark .reviews-pagination { border-color: #334155; }
.pagination-btn { padding: 0.375rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; background: #fff; color: #0f172a; cursor: pointer; font-size: 0.875rem; }
.pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; }
:root.dark .pagination-btn { background: #1e293b; color: #e2e8f0; border-color: #334155; }
.pagination-info { font-size: 0.875rem; color: #64748b; }
</style>
