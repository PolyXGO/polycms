<template>
    <div class="product-reviews" id="reviews">
        <!-- Header -->
        <div class="product-reviews__header">
            <h3 class="product-reviews__title">Customer Reviews</h3>
            <div v-if="stats.total > 0" class="product-reviews__summary">
                <div class="product-reviews__avg">
                    <span class="product-reviews__avg-value">{{ stats.average }}</span>
                    <div class="product-reviews__stars">
                        <span v-for="star in 5" :key="star" class="star" :class="{ 'star--filled': star <= Math.round(stats.average) }">★</span>
                    </div>
                    <span class="product-reviews__count">{{ stats.total }} review{{ stats.total !== 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>

        <!-- Write Review (if can review) -->
        <div v-if="showReviewForm" class="product-reviews__form">
            <h4 class="product-reviews__form-title">Write a Review</h4>
            <div class="product-reviews__rating-input">
                <label>Your Rating</label>
                <div class="rating-selector">
                    <button
                        v-for="star in 5" :key="star"
                        type="button"
                        @click="reviewForm.rating = star"
                        @mouseenter="hoverRating = star"
                        @mouseleave="hoverRating = 0"
                        class="rating-star"
                        :class="{ 'rating-star--active': star <= (hoverRating || reviewForm.rating) }"
                    >★</button>
                </div>
            </div>
            <input
                v-model="reviewForm.title"
                type="text"
                placeholder="Review title (optional)"
                class="product-reviews__input"
            />
            <textarea
                v-model="reviewForm.content"
                rows="4"
                placeholder="Share your experience with this product..."
                class="product-reviews__textarea"
            ></textarea>
            <div class="product-reviews__form-actions">
                <button @click="submitReview" :disabled="submitting || !reviewForm.rating" class="product-reviews__submit-btn">
                    {{ submitting ? 'Submitting...' : 'Submit Review' }}
                </button>
            </div>
            <p v-if="submitMessage" class="product-reviews__message" :class="{ 'product-reviews__message--error': submitError }">
                {{ submitMessage }}
            </p>
        </div>

        <!-- Reviews List -->
        <div v-if="reviews.length > 0" class="product-reviews__list">
            <div v-for="review in reviews" :key="review.id" class="review-item">
                <div class="review-item__header">
                    <div class="review-item__meta">
                        <div class="review-item__stars">
                            <span v-for="star in 5" :key="star" class="star" :class="{ 'star--filled': star <= review.rating }">★</span>
                        </div>
                        <span v-if="review.verified_purchase" class="review-item__verified">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Verified Purchase
                        </span>
                    </div>
                    <span class="review-item__date">{{ formatDate(review.created_at) }}</span>
                </div>
                <h5 v-if="review.title" class="review-item__title">{{ review.title }}</h5>
                <p class="review-item__content">{{ review.content }}</p>
                <span class="review-item__author">by {{ review.user?.name || 'Anonymous' }}</span>
            </div>
        </div>
        <div v-else-if="!loading" class="product-reviews__empty">
            <p>No reviews yet. Be the first to review this product!</p>
        </div>

        <!-- Load More -->
        <button v-if="hasMore" @click="loadMore" :disabled="loading" class="product-reviews__load-more">
            {{ loading ? 'Loading...' : 'Load More Reviews' }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    productId: { type: Number, required: true },
});

interface Review {
    id: number;
    rating: number;
    title?: string;
    content?: string;
    verified_purchase: boolean;
    user?: { name: string; avatar?: string };
    created_at: string;
}

const reviews = ref<Review[]>([]);
const stats = reactive({ total: 0, average: 0 });
const loading = ref(false);
const page = ref(1);
const hasMore = ref(false);
const showReviewForm = ref(true);
const submitting = ref(false);
const submitMessage = ref('');
const submitError = ref(false);
const hoverRating = ref(0);
const reviewForm = reactive({ rating: 0, title: '', content: '' });

function formatDate(dateStr: string): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

async function loadReviews(pageNum = 1) {
    loading.value = true;
    try {
        const { data } = await axios.get(`/api/v1/products/${props.productId}/reviews`, {
            params: { page: pageNum, per_page: 5 },
        });
        const newReviews = data.reviews?.data || [];
        if (pageNum === 1) {
            reviews.value = newReviews;
        } else {
            reviews.value.push(...newReviews);
        }
        stats.total = data.stats?.total || 0;
        stats.average = data.stats?.average || 0;
        page.value = pageNum;
        hasMore.value = (data.reviews?.current_page || 1) < (data.reviews?.last_page || 1);
    } catch (e) {
        console.error('Failed to load reviews:', e);
    } finally {
        loading.value = false;
    }
}

async function loadMore() {
    await loadReviews(page.value + 1);
}

async function submitReview() {
    if (!reviewForm.rating) return;
    submitting.value = true;
    submitMessage.value = '';
    submitError.value = false;

    try {
        await axios.post(`/api/v1/products/${props.productId}/reviews`, {
            rating: reviewForm.rating,
            title: reviewForm.title || undefined,
            content: reviewForm.content || undefined,
        });
        submitMessage.value = 'Thank you! Your review has been submitted for moderation.';
        reviewForm.rating = 0;
        reviewForm.title = '';
        reviewForm.content = '';
        showReviewForm.value = false;
    } catch (e: any) {
        submitError.value = true;
        if (e.response?.status === 401) {
            submitMessage.value = 'Please log in to submit a review.';
        } else if (e.response?.status === 422) {
            submitMessage.value = e.response.data?.message || 'You have already reviewed this product.';
        } else {
            submitMessage.value = 'Failed to submit review. Please try again.';
        }
    } finally {
        submitting.value = false;
    }
}

onMounted(() => loadReviews());
</script>

<style scoped>
.product-reviews { margin-top: 3rem; }
.product-reviews__header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.product-reviews__title { font-size: 1.375rem; font-weight: 700; color: #0f172a; }
.product-reviews__summary { display: flex; align-items: center; }
.product-reviews__avg { display: flex; align-items: center; gap: 0.5rem; }
.product-reviews__avg-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
.product-reviews__stars { display: flex; gap: 1px; }
.product-reviews__count { font-size: 0.875rem; color: #64748b; }
.star { color: #d1d5db; font-size: 1rem; }
.star--filled { color: #f59e0b; }

.product-reviews__form { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem; }
.product-reviews__form-title { font-size: 1rem; font-weight: 600; color: #0f172a; margin-bottom: 1rem; }
.product-reviews__rating-input { margin-bottom: 1rem; }
.product-reviews__rating-input label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem; }
.rating-selector { display: flex; gap: 0.25rem; }
.rating-star { background: none; border: none; font-size: 1.5rem; color: #d1d5db; cursor: pointer; padding: 0; transition: all 0.15s; }
.rating-star--active { color: #f59e0b; transform: scale(1.1); }
.rating-star:hover { transform: scale(1.2); }
.product-reviews__input, .product-reviews__textarea { width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 0.75rem; background: #fff; color: #0f172a; }
.product-reviews__input:focus, .product-reviews__textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
.product-reviews__form-actions { display: flex; justify-content: flex-end; }
.product-reviews__submit-btn { padding: 0.5rem 1.25rem; background: #4f46e5; color: #fff; border: none; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; cursor: pointer; transition: background 0.15s; }
.product-reviews__submit-btn:hover { background: #4338ca; }
.product-reviews__submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.product-reviews__message { margin-top: 0.75rem; font-size: 0.875rem; color: #059669; }
.product-reviews__message--error { color: #dc2626; }

.product-reviews__list { display: flex; flex-direction: column; gap: 1rem; }
.review-item { padding: 1.25rem; background: #fff; border: 1px solid #f1f5f9; border-radius: 0.75rem; }
.review-item__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
.review-item__meta { display: flex; align-items: center; gap: 0.75rem; }
.review-item__stars { display: flex; gap: 1px; }
.review-item__verified { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.6875rem; color: #059669; font-weight: 500; }
.review-item__date { font-size: 0.75rem; color: #94a3b8; }
.review-item__title { font-weight: 600; font-size: 0.9375rem; color: #0f172a; margin-bottom: 0.25rem; }
.review-item__content { font-size: 0.875rem; color: #334155; line-height: 1.6; }
.review-item__author { display: block; margin-top: 0.5rem; font-size: 0.75rem; color: #94a3b8; font-style: italic; }

.product-reviews__empty { text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem; }
.product-reviews__load-more { display: block; width: 100%; padding: 0.75rem; margin-top: 1rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #fff; color: #4f46e5; font-weight: 500; font-size: 0.875rem; cursor: pointer; transition: all 0.15s; }
.product-reviews__load-more:hover { background: #f8fafc; border-color: #c7d2fe; }
.product-reviews__load-more:disabled { opacity: 0.5; cursor: not-allowed; }

/* Dark mode */
:root.dark .product-reviews__title,
:root.dark .product-reviews__avg-value { color: #f1f5f9; }
:root.dark .product-reviews__form { background: #1e293b; border-color: #334155; }
:root.dark .product-reviews__form-title { color: #f1f5f9; }
:root.dark .product-reviews__input, :root.dark .product-reviews__textarea { background: #0f172a; border-color: #334155; color: #f1f5f9; }
:root.dark .review-item { background: #1e293b; border-color: #334155; }
:root.dark .review-item__title { color: #f1f5f9; }
:root.dark .review-item__content { color: #cbd5e1; }
</style>
