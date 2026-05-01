<template>
    <div class="p-6 max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Reports</h1>

        <div v-if="reportCards.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <p class="text-lg font-medium mb-1">No reports available</p>
            <p class="text-sm">Reports are registered by themes and modules.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <router-link
                v-for="card in reportCards"
                :key="card.slug"
                :to="{ name: 'admin.reports.show', params: { slug: card.slug } }"
                class="block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md transition-all group"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ card.label }}
                        </h3>
                        <p v-if="card.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ card.description }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface ReportCard {
    slug: string;
    label: string;
    description?: string;
}

const reportCards = ref<ReportCard[]>([]);

onMounted(async () => {
    // Get report cards from menu children
    try {
        const response = await axios.get('/api/v1/admin-menu');
        const menus = response.data?.data || [];
        const reportsMenu = menus.find((m: any) => m.key === 'reports');
        if (reportsMenu?.children?.length) {
            reportCards.value = reportsMenu.children.map((child: any) => ({
                slug: child.routeParams?.slug || child.key.replace('reports-', ''),
                label: child.label,
                description: child.description || null,
            }));
        }
    } catch {
        // Fallback if menu API fails
    }
});
</script>
