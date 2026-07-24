<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50 pt-16">
        <!-- Topbar is handled by global app.blade.php + TopbarMenu component -->
        <!-- We just need padding-top (pt-16) to account for the fixed topbar -->

        <!-- Page Heading -->
        <header class="bg-white shadow" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>
