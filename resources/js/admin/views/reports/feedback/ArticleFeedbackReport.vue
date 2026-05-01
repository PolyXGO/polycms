<template>
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <router-link :to="{ name: 'admin.reports.index' }" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-1 inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Reports
                </router-link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Article Feedback</h1>
            </div>
            <div class="flex gap-2">
                <select v-model="dateRange" @change="fetchData" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="all">All time</option>
                </select>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-16">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <template v-else>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Votes</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Helpful</p>
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ stats.helpful }}
                        <span v-if="stats.total > 0" class="text-base font-normal text-gray-400">({{ helpfulPercent }}%)</span>
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Not Helpful</p>
                    <p class="text-3xl font-bold text-red-500 dark:text-red-400 mt-1">
                        {{ stats.notHelpful }}
                        <span v-if="stats.total > 0" class="text-base font-normal text-gray-400">({{ notHelpfulPercent }}%)</span>
                    </p>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily Trend</h2>
                <canvas ref="chartCanvas" height="100"></canvas>
            </div>

            <!-- Votes Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Votes</h2>
                    <select v-model="filterType" @change="() => fetchVotes()" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">All</option>
                        <option value="helpful">Helpful</option>
                        <option value="not_helpful">Not Helpful</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Article</th>
                                <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Vote</th>
                                <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">IP</th>
                                <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="vote in votes" :key="vote.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-3">
                                    <a v-if="vote.content_url" :href="vote.content_url" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ vote.content_title || `#${vote.voteable_id}` }}
                                    </a>
                                    <span v-else class="text-gray-700 dark:text-gray-300">{{ vote.content_title || `#${vote.voteable_id}` }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    <span v-if="vote.type === 'helpful'" class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 px-2 py-0.5 rounded-full text-xs font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                        Helpful
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 px-2 py-0.5 rounded-full text-xs font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path></svg>
                                        Not Helpful
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ vote.ip_address }}</td>
                                <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ formatDate(vote.created_at) }}</td>
                            </tr>
                            <tr v-if="votes.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400 dark:text-gray-500">No votes recorded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="pagination.lastPage > 1" class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Page {{ pagination.currentPage }} of {{ pagination.lastPage }} ({{ pagination.total }} total)
                    </span>
                    <div class="flex gap-2">
                        <button @click="goToPage(pagination.currentPage - 1)" :disabled="pagination.currentPage <= 1"
                                class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            Previous
                        </button>
                        <button @click="goToPage(pagination.currentPage + 1)" :disabled="pagination.currentPage >= pagination.lastPage"
                                class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

const loading = ref(true);
const dateRange = ref('30');
const filterType = ref('');
const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const stats = ref({ total: 0, helpful: 0, notHelpful: 0 });
const trendData = ref<any[]>([]);
const votes = ref<any[]>([]);
const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 });

const helpfulPercent = computed(() =>
    stats.value.total > 0 ? Math.round((stats.value.helpful / stats.value.total) * 100) : 0
);
const notHelpfulPercent = computed(() =>
    stats.value.total > 0 ? Math.round((stats.value.notHelpful / stats.value.total) * 100) : 0
);

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function fetchData() {
    loading.value = true;
    try {
        const params: any = { source: 'flexidocs', voteable_type: 'post' };
        if (dateRange.value !== 'all') {
            const from = new Date();
            from.setDate(from.getDate() - parseInt(dateRange.value));
            params.from = from.toISOString().split('T')[0];
        }

        const [statsRes, votesRes] = await Promise.all([
            axios.get('/api/v1/content-votes/stats', { params }),
            axios.get('/api/v1/content-votes', { params: { ...params, per_page: 20, type: filterType.value || undefined } }),
        ]);

        const d = statsRes.data?.data;
        stats.value = {
            total: d?.total || 0,
            helpful: d?.by_type?.helpful || 0,
            notHelpful: d?.by_type?.not_helpful || 0,
        };
        trendData.value = d?.trend || [];

        const vd = votesRes.data?.data;
        votes.value = vd?.data || [];
        pagination.value = {
            currentPage: vd?.current_page || 1,
            lastPage: vd?.last_page || 1,
            total: vd?.total || 0,
        };

        await nextTick();
        renderChart();
    } catch (e) {
        console.error('Failed to fetch report data:', e);
    } finally {
        loading.value = false;
    }
}

async function fetchVotes(page = 1) {
    const params: any = { source: 'flexidocs', voteable_type: 'post', per_page: 20, page };
    if (dateRange.value !== 'all') {
        const from = new Date();
        from.setDate(from.getDate() - parseInt(dateRange.value));
        params.from = from.toISOString().split('T')[0];
    }
    if (filterType.value) params.type = filterType.value;

    try {
        const res = await axios.get('/api/v1/content-votes', { params });
        const vd = res.data?.data;
        votes.value = vd?.data || [];
        pagination.value = {
            currentPage: vd?.current_page || 1,
            lastPage: vd?.last_page || 1,
            total: vd?.total || 0,
        };
    } catch (e) {
        console.error('Failed to fetch votes:', e);
    }
}

function goToPage(p: number) {
    fetchVotes(p);
}

function renderChart() {
    if (!chartCanvas.value) return;
    if (chartInstance) chartInstance.destroy();

    // Build date labels
    const dateMap: Record<string, { helpful: number; not_helpful: number }> = {};
    trendData.value.forEach((item: any) => {
        if (!dateMap[item.date]) dateMap[item.date] = { helpful: 0, not_helpful: 0 };
        dateMap[item.date][item.type as 'helpful' | 'not_helpful'] = item.count;
    });

    const labels = Object.keys(dateMap).sort();
    const helpfulData = labels.map(d => dateMap[d].helpful);
    const notHelpfulData = labels.map(d => dateMap[d].not_helpful);

    const isDark = document.documentElement.classList.contains('dark');

    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: labels.map(d => {
                const date = new Date(d);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [
                {
                    label: 'Helpful',
                    data: helpfulData,
                    backgroundColor: isDark ? 'rgba(52, 211, 153, 0.7)' : 'rgba(16, 185, 129, 0.7)',
                    borderRadius: 4,
                },
                {
                    label: 'Not Helpful',
                    data: notHelpfulData,
                    backgroundColor: isDark ? 'rgba(248, 113, 113, 0.7)' : 'rgba(239, 68, 68, 0.7)',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { color: isDark ? '#9ca3af' : '#6b7280' } },
            },
            scales: {
                x: { stacked: true, ticks: { color: isDark ? '#9ca3af' : '#6b7280' }, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { color: isDark ? '#9ca3af' : '#6b7280', stepSize: 1 }, grid: { color: isDark ? 'rgba(75,85,99,0.3)' : 'rgba(229,231,235,0.8)' } },
            },
        },
    });
}

onMounted(() => fetchData());
</script>
