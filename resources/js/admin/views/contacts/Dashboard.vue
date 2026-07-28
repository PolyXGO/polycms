<template>
  <div class="contacts-dashboard">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Contact Dashboard') }}</h1>
      <p class="text-sm text-admin-theme-text-secondary mt-1">{{ t('Key metrics and submission trends for contact forms') }}</p>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Total Submissions -->
      <div class="group relative p-6 bg-admin-theme-surface rounded-2xl border border-admin-theme-border shadow-sm hover:shadow-md transition-all duration-300">
        <div class="flex justify-between items-start">
          <div>
            <span class="text-sm font-semibold text-admin-theme-text-secondary">{{ t('Total Submissions') }}</span>
            <h3 class="text-3xl font-bold text-admin-theme-text mt-2">{{ stats.total_submissions }}</h3>
          </div>
          <div class="p-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
          </div>
        </div>
        <div class="text-xs text-admin-theme-text-muted mt-4">{{ t('All-time submissions across all forms') }}</div>
      </div>

      <!-- Unread Submissions -->
      <div class="group relative p-6 bg-admin-theme-surface rounded-2xl border border-admin-theme-border shadow-sm hover:shadow-md transition-all duration-300">
        <div class="flex justify-between items-start">
          <div>
            <span class="text-sm font-semibold text-admin-theme-text-secondary">{{ t('Unread Messages') }}</span>
            <h3 class="text-3xl font-bold text-admin-theme-text mt-2">{{ stats.unread_submissions }}</h3>
          </div>
          <div class="p-3 rounded-xl bg-red-500/10 text-red-600 dark:text-red-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
        <div class="text-xs text-admin-theme-text-muted mt-4">{{ t('Requires review and action') }}</div>
      </div>

      <!-- Submissions Types Summary -->
      <div class="group relative p-6 bg-admin-theme-surface rounded-2xl border border-admin-theme-border shadow-sm hover:shadow-md transition-all duration-300">
        <div class="flex justify-between items-start">
          <div>
            <span class="text-sm font-semibold text-admin-theme-text-secondary">{{ t('Active Form Types') }}</span>
            <div class="flex gap-4 mt-2">
              <div v-for="tInfo in stats.by_type" :key="tInfo.type" class="flex flex-col">
                <span class="text-xs uppercase font-mono text-admin-theme-text-secondary">{{ tInfo.type }}</span>
                <span class="text-lg font-bold text-admin-theme-text">{{ tInfo.count }}</span>
              </div>
              <span v-if="stats.by_type.length === 0" class="text-sm text-admin-theme-text-muted mt-1">{{ t('No type data') }}</span>
            </div>
          </div>
          <div class="p-3 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
        </div>
        <div class="text-xs text-admin-theme-text-muted mt-4">{{ t('Categories of form submittals') }}</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
      <!-- Submission Trends Chart -->
      <div class="lg:col-span-8 bg-admin-theme-surface rounded-2xl border border-admin-theme-border p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-admin-theme-text mb-6 pb-2 border-b border-admin-theme-border">
          {{ t('Submission Trends (Last 30 Days)') }}
        </h3>
        <div class="w-full relative h-[300px]">
          <canvas ref="chartCanvas" height="150"></canvas>
        </div>
      </div>

      <!-- Recent Unread Submissions -->
      <div class="lg:col-span-4 bg-admin-theme-surface rounded-2xl border border-admin-theme-border p-6 shadow-sm flex flex-col">
        <div class="flex justify-between items-center mb-6 pb-2 border-b border-admin-theme-border">
          <h3 class="text-lg font-semibold text-admin-theme-text">{{ t('Recent Unread') }}</h3>
          <router-link
            :to="{ name: 'admin.contacts.submissions', query: { status: 'unread' } }"
            class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
          >
            {{ t('View All') }}
          </router-link>
        </div>

        <div class="flex-1 space-y-4">
          <div
            v-for="sub in stats.recent_unread"
            :key="sub.id"
            class="p-3 bg-admin-theme-base/20 hover:bg-admin-theme-base/30 rounded-xl border border-admin-theme-border/50 transition-colors flex justify-between items-center cursor-pointer"
            @click="viewSubmission(sub)"
          >
            <div class="min-w-0 flex-1 pr-3">
              <div class="text-sm font-semibold text-admin-theme-text truncate">{{ sub.name || t('Anonymous') }}</div>
              <div class="text-xs text-admin-theme-text-muted truncate mt-0.5">{{ sub.email || '-' }}</div>
              <div class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-blue-100/50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 inline-block mt-2">
                {{ sub.form?.name || sub.type }}
              </div>
            </div>
            <div class="text-xs text-admin-theme-text-muted flex-shrink-0 text-right">
              {{ formatTimeAgo(sub.created_at) }}
            </div>
          </div>

          <div v-if="stats.recent_unread.length === 0" class="text-center py-12 text-admin-theme-text-secondary flex flex-col items-center justify-center gap-2">
            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-semibold">{{ t('Inbox Clean! No unread messages.') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Chart from 'chart.js/auto';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();
const router = useRouter();

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const stats = ref<{
  total_submissions: number;
  unread_submissions: number;
  by_type: Array<{ type: string; count: number }>;
  daily_stats: Array<{ date: string; count: number }>;
  recent_unread: any[];
}>({
  total_submissions: 0,
  unread_submissions: 0,
  by_type: [],
  daily_stats: [],
  recent_unread: []
});

const loadStats = async () => {
  try {
    const response = await axios.get('/api/v1/contacts/reports');
    stats.value = response.data;
    
    // Render the chart
    await nextTick();
    renderChart();
  } catch (e) {
    console.error(e);
    dialog.error(t('Failed to load dashboard data'));
  }
};

const renderChart = () => {
  if (!chartCanvas.value) return;
  if (chartInstance) chartInstance.destroy();

  const labels = stats.value.daily_stats.map(s => {
    const date = new Date(s.date);
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
  });
  const data = stats.value.daily_stats.map(s => s.count);

  const isDark = document.documentElement.classList.contains('dark');

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: t('Submissions'),
          data: data,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.3,
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: '#3b82f6'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            color: isDark ? '#9ca3af' : '#6b7280'
          },
          grid: {
            color: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)'
          }
        },
        x: {
          ticks: {
            color: isDark ? '#9ca3af' : '#6b7280',
            maxRotation: 45,
            autoSkip: true,
            maxTicksLimit: 10
          },
          grid: {
            display: false
          }
        }
      }
    }
  });
};

const viewSubmission = (sub: any) => {
  router.push({ name: 'admin.contacts.submissions' });
};

// Generates simple "3 hours ago" etc. strings
const formatTimeAgo = (dateStr: string) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const seconds = Math.floor((new Date().getTime() - date.getTime()) / 1000);
  
  let interval = Math.floor(seconds / 31536000);
  if (interval >= 1) return interval + 'y ' + t('ago');
  interval = Math.floor(seconds / 2592000);
  if (interval >= 1) return interval + 'm ' + t('ago');
  interval = Math.floor(seconds / 86400);
  if (interval >= 1) return interval + 'd ' + t('ago');
  interval = Math.floor(seconds / 3600);
  if (interval >= 1) return interval + 'h ' + t('ago');
  interval = Math.floor(seconds / 60);
  if (interval >= 1) return interval + 'm ' + t('ago');
  return t('just now');
};

onMounted(() => {
  loadStats();
});
</script>

<style scoped>
.contacts-dashboard {
  max-width: 100%;
}
</style>
