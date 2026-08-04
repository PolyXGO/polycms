<template>
    <AccountLayout>
        <Head :title="t('API Keys Portal')" />
        <div class="space-y-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">API Keys</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Manage your PolyFengshui API keys. Each key is shown in full only once at creation time.
                </p>
            </div>

            <!-- Usage Summary -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800 p-5">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Today's Requests</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatNumber(usage?.today) }}</div>
                </div>
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800 p-5">
                    <div class="text-sm text-gray-500 dark:text-gray-400">This Month</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatNumber(usage?.this_month) }}</div>
                </div>
            </div>

            <!-- Create New Token -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create New API Key</h2>
                <form @submit.prevent="createToken" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Name</label>
                            <input type="text" v-model="newToken.name" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="My App" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Allowed Domain (optional)</label>
                            <input type="text" v-model="newToken.domain"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="example.com" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Package / Plan</label>
                            <select v-model="newToken.package_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option :value="null">-- Free Developer Plan ($0.00/mo) --</option>
                                <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                    {{ pkg.name }} (${{ pkg.price_monthly }}/mo - {{ formatNumber(pkg.api_limit_daily) }} req/day)
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Selected Package Details Box -->
                    <div v-if="selectedPackageInfo" class="p-3.5 rounded-lg bg-blue-50/60 dark:bg-zinc-800/80 border border-blue-200/80 dark:border-zinc-700 text-xs space-y-1.5">
                        <div class="font-bold text-blue-900 dark:text-blue-300 flex items-center justify-between">
                            <span>Selected Plan: {{ selectedPackageInfo.name }} (${{ selectedPackageInfo.price_monthly }}/mo)</span>
                            <span v-if="selectedPackageInfo.has_ai" class="px-2 py-0.5 text-[10px] bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 font-bold rounded">AI Enabled</span>
                        </div>
                        <div class="text-gray-700 dark:text-gray-300 flex flex-wrap gap-4">
                            <span>API Daily: <strong>{{ formatNumber(selectedPackageInfo.api_limit_daily) }}</strong> req/day</span>
                            <span>API Monthly: <strong>{{ formatNumber(selectedPackageInfo.api_limit_monthly) }}</strong> req/mo</span>
                            <span v-if="selectedPackageInfo.has_ai">AI Daily: <strong>{{ formatNumber(selectedPackageInfo.ai_limit_daily) }}</strong> req/day</span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            Scopes: <span class="font-medium text-gray-700 dark:text-gray-300">{{ selectedPackageInfo.is_full_access ? 'Full Access' : (selectedPackageInfo.scope_groups || []).map((s:any) => s.label).join(', ') || 'Standard' }}</span>
                        </div>
                    </div>

                    <button type="submit" :disabled="creating"
                        class="inline-flex items-center px-4 py-2 bg-black dark:bg-white text-white dark:text-black text-sm font-semibold rounded-md hover:opacity-80 disabled:opacity-50 transition">
                        {{ creating ? 'Creating...' : 'Create Key' }}
                    </button>
                </form>

                <!-- Show plain key once on creation -->
                <div v-if="plainKey" class="mt-4 p-4 bg-green-50 dark:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-800">
                    <p class="text-sm font-medium text-green-700 dark:text-green-300 mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Copy this API key now. It will not be shown again.</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly :value="plainKey" @click="($event.target as HTMLInputElement).select()"
                            class="flex-1 p-2.5 bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700 text-xs font-mono text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        <button @click="copyText(plainKey)"
                            class="px-3.5 py-2 text-xs font-semibold rounded transition-all duration-200 flex items-center gap-1.5 shadow-sm"
                            :class="copiedKey === plainKey ? 'bg-emerald-600 text-white ring-2 ring-emerald-400/50' : 'bg-green-600 hover:bg-green-700 text-white'">
                            <svg v-if="copiedKey === plainKey" class="w-3.5 h-3.5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ copiedKey === plainKey ? 'Copied!' : 'Copy Key' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Token List -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800">
                <div class="p-6 border-b border-gray-200 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Your API Keys</h2>
                </div>
                <div v-if="tokens.length === 0" class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No API keys yet. Create one above.</p>
                </div>
                <div v-else class="divide-y divide-gray-200 dark:divide-zinc-800">
                    <div v-for="token in tokens" :key="token.id" class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-gray-900 dark:text-white">{{ token.name }}</span>
                                <span :class="{
                                    'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300': token.is_active && token.payment_status !== 'pending_payment',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 ring-1 ring-amber-500/40': token.payment_status === 'pending_payment',
                                    'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300': !token.is_active && token.payment_status !== 'pending_payment'
                                }" class="px-2 py-0.5 text-xs font-semibold rounded">
                                    {{ token.payment_status === 'pending_payment' ? 'Pending Payment' : (token.is_active ? 'Active' : 'Revoked') }}
                                </span>
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span>Domain: <strong>{{ token.domain || 'Any domain' }}</strong></span>
                                <span>Created: {{ new Date(token.created_at).toLocaleDateString() }}</span>
                                <span class="font-mono text-gray-400 dark:text-gray-500">Key: {{ token.prefix }}</span>
                                <span v-if="token.order_code" class="text-blue-600 dark:text-blue-400">
                                    Order: <a :href="token.order_url" target="_blank" class="font-mono font-semibold underline hover:opacity-80">{{ token.order_code }}</a>
                                </span>
                            </div>

                            <!-- Package Details Badge -->
                            <div class="mt-2 inline-flex flex-wrap items-center gap-2 p-2 rounded-md bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 text-xs">
                                <span class="font-semibold text-blue-600 dark:text-blue-400">Package: {{ token.package_name }}</span>
                                <template v-if="token.package_info">
                                    <span class="text-gray-300 dark:text-zinc-600">•</span>
                                    <span class="text-gray-600 dark:text-gray-300">API: {{ formatNumber(token.package_info.api_limit_daily) }}/day</span>
                                    <span class="text-gray-300 dark:text-zinc-600">•</span>
                                    <span class="text-gray-600 dark:text-gray-300">Monthly: {{ formatNumber(token.package_info.api_limit_monthly) }}/mo</span>
                                    <template v-if="token.package_info.has_ai">
                                        <span class="text-gray-300 dark:text-zinc-600">•</span>
                                        <span class="text-amber-600 dark:text-amber-400">AI: {{ formatNumber(token.package_info.ai_limit_daily) }}/day</span>
                                    </template>
                                    <span class="text-gray-300 dark:text-zinc-600">•</span>
                                    <span class="text-gray-500 dark:text-gray-400">Scopes: {{ (token.package_info.scope_labels || []).join(', ') }}</span>
                                </template>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button v-if="token.payment_status === 'pending_payment'" @click="openCheckoutForToken(token)"
                                class="px-3 py-1.5 text-xs font-bold rounded-md bg-amber-500 hover:bg-amber-600 text-white shadow transition animate-pulse flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Complete Payment (${{ token.package_info?.price_monthly }})</span>
                            </button>
                            <template v-else>
                                <button @click="regenerateToken(token.id)"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-md border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition">
                                    Regenerate Key
                                </button>
                                <button @click="toggleToken(token.id)"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-md border transition"
                                    :class="token.is_active
                                        ? 'border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800'
                                        : 'border-green-500 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30'">
                                    {{ token.is_active ? 'Revoke Key' : 'Activate Key' }}
                                </button>
                            </template>
                            <button @click="deleteToken(token.id)"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available API Packages / Plans -->
            <div v-if="packages && packages.length > 0" class="space-y-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Available API Subscription Packages</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Choose an API tier tailored to your platform traffic and data requirements.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div v-for="pkg in packages" :key="pkg.id"
                        class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800 p-6 flex flex-col justify-between hover:shadow-lg transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ pkg.name }}</h3>
                                <span v-if="pkg.has_ai" class="px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 rounded-full">
                                    AI Enabled
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ pkg.description }}</p>

                            <div class="mt-4 pb-4 border-b border-gray-100 dark:border-zinc-800">
                                <span class="text-2xl font-extrabold text-gray-900 dark:text-white">${{ pkg.price_monthly }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400"> / month</span>
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    ${{ pkg.price_yearly }} / year
                                </div>
                            </div>

                            <ul class="mt-4 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>API Limit: <strong>{{ formatNumber(pkg.api_limit_daily) }}</strong> req/day</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Monthly: <strong>{{ formatNumber(pkg.api_limit_monthly) }}</strong> req/mo</span>
                                </li>
                                <li v-if="pkg.has_ai" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>AI Limit: <strong>{{ formatNumber(pkg.ai_limit_daily) }}</strong> req/day</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Scope: <strong>{{ pkg.is_full_access ? 'Full Access' : (pkg.scope_groups || []).map((s:any) => s.label).join(', ') || 'Standard' }}</strong></span>
                                </li>
                            </ul>
                        </div>

                        <button @click="selectPackageForNewKey(pkg.id)"
                            class="mt-6 w-full py-2 px-3 text-xs font-semibold rounded-lg border border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200 transition text-center">
                            Select Plan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Regenerate Key Modal -->
            <div v-if="showRegenModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800 max-w-lg w-full p-6 space-y-4 shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            <span>API Key Regenerated</span>
                        </h3>
                        <button @click="showRegenModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg">✕</button>
                    </div>

                    <p class="text-xs text-amber-600 dark:text-amber-400 leading-relaxed font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Your previous key secret has been invalidated. Please copy your new secret key below. It will be shown ONCE and cannot be recovered later.</span>
                    </p>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">New Secret Key:</label>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly :value="regenSecretKey" @click="($event.target as HTMLInputElement).select()"
                                class="flex-1 p-3 bg-gray-50 dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700 text-xs font-mono text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            <button @click="copyText(regenSecretKey)"
                                class="px-4 py-3 text-xs font-bold rounded-lg transition-all duration-200 shrink-0"
                                :class="copiedKey === regenSecretKey ? 'bg-emerald-600 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'">
                                {{ copiedKey === regenSecretKey ? 'Copied!' : 'Copy Key' }}
                            </button>
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end">
                        <button @click="showRegenModal = false"
                            class="px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-black text-xs font-semibold rounded-lg hover:opacity-80 transition">
                            Done
                        </button>
                    </div>
                </div>
            </div>

            <!-- Checkout / Payment Modal -->
            <div v-if="showCheckoutModal && pendingPayToken" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800 max-w-lg w-full p-6 space-y-4 shadow-2xl my-8">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Payment & Checkout — Order #{{ pendingPayToken.order_code || 'Pending' }}</span>
                        </h3>
                        <button @click="showCheckoutModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg">✕</button>
                    </div>

                    <!-- Plan Summary -->
                    <div class="p-4 rounded-lg bg-blue-50/70 dark:bg-zinc-800/80 border border-blue-200/70 dark:border-zinc-700 space-y-2">
                        <div class="flex items-center justify-between font-bold text-gray-900 dark:text-white text-sm">
                            <span>Key: {{ pendingPayToken.name }}</span>
                            <span class="text-blue-600 dark:text-blue-400 font-extrabold text-base">${{ pendingPayToken.package_info?.price_monthly }}/mo</span>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            Package: <strong class="text-gray-900 dark:text-white">{{ pendingPayToken.package_name }}</strong>
                        </div>
                        <div v-if="pendingPayToken.order_code" class="text-xs text-gray-600 dark:text-gray-300 flex items-center justify-between">
                            <span>Order Reference: <strong class="font-mono text-gray-900 dark:text-white">{{ pendingPayToken.order_code }}</strong></span>
                            <a :href="pendingPayToken.order_url" target="_blank" class="font-bold underline text-blue-600 dark:text-blue-400 hover:opacity-80 flex items-center gap-1">
                                <span>View in Orders</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- SePay VietQR Payment Section (if available) -->
                    <div v-if="paymentInfo?.sepay_payment" class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 space-y-3">
                        <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h-1M5 12H4m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707"/></svg>
                                <span>Scan VietQR Code to Pay (SePay)</span>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 rounded font-bold">Auto Verification</span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <img :src="paymentInfo.sepay_payment.qr_url" alt="SePay VietQR Code" class="w-36 h-36 object-contain bg-white p-1 rounded-lg border border-gray-200 dark:border-zinc-700 shadow-sm" />
                            <div class="text-xs space-y-2 flex-1">
                                <div class="flex justify-between items-center pb-1.5 border-b border-gray-200/60 dark:border-zinc-700/60">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Bank:</span>
                                    <strong class="text-gray-900 dark:text-white font-semibold">{{ paymentInfo.sepay_payment.bank_name || 'MBBank / Vietinbank' }}</strong>
                                </div>
                                <div class="flex justify-between items-center pb-1.5 border-b border-gray-200/60 dark:border-zinc-700/60">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Account No:</span>
                                    <strong class="font-mono text-gray-900 dark:text-white font-bold select-all">{{ paymentInfo.sepay_payment.account_number }}</strong>
                                </div>
                                <div class="flex justify-between items-center pb-1.5 border-b border-gray-200/60 dark:border-zinc-700/60">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Account Holder:</span>
                                    <strong class="uppercase text-gray-900 dark:text-white font-semibold">{{ paymentInfo.sepay_payment.account_holder }}</strong>
                                </div>
                                <div class="flex justify-between items-center pt-0.5">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Amount:</span>
                                    <strong class="text-green-600 dark:text-emerald-400 font-extrabold text-sm">${{ pendingPayToken.package_info?.price_monthly }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Code Box -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Transfer Content / Memo Code (Mandatory):</label>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly :value="pendingPayToken.order_code" @click="($event.target as HTMLInputElement).select()"
                                class="flex-1 p-2.5 bg-gray-100 dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700 text-sm font-mono font-bold text-gray-900 dark:text-gray-100 select-all" />
                            <button @click="copyText(pendingPayToken.order_code)"
                                class="px-3.5 py-2 text-xs font-semibold rounded bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm flex items-center gap-1">
                                <svg v-if="copiedKey === pendingPayToken.order_code" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ copiedKey === pendingPayToken.order_code ? 'Copied!' : 'Copy Code' }}</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Include this exact order code in your bank transfer memo to automatically verify payment.</p>
                    </div>

                    <!-- Payment Action Buttons -->
                    <div class="pt-3 space-y-2 border-t border-gray-100 dark:border-zinc-800">
                        <div class="flex flex-col sm:flex-row items-center gap-2">
                            <button @click="checkPaymentStatus(pendingPayToken.id)" :disabled="checkingPayment"
                                class="w-full sm:flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow disabled:opacity-50 transition flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" :class="checkingPayment ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>{{ checkingPayment ? 'Checking Payment...' : 'Check Payment Status' }}</span>
                            </button>

                            <a :href="pendingPayToken.order_url" target="_blank"
                                class="w-full sm:flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-800 dark:text-gray-200 text-xs font-semibold rounded-lg transition text-center flex items-center justify-center gap-1.5">
                                <span>Go to Order Checkout</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <button @click="showCheckoutModal = false" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                Close Window
                            </button>
                            <button @click="completePayment" :disabled="processingPayment"
                                class="text-xs text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Mark Paid (Instant Test / Demo)</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>

<script setup lang="ts">
import { ref, computed, defineProps } from 'vue';
import { Head } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import axios from 'axios';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();
const dialog = useDialog();

const props = defineProps<{
    tokens: Array<{
        id: number;
        name: string;
        domain: string | null;
        prefix: string;
        is_active: boolean;
        payment_status?: string;
        package_name: string;
        package_info?: any;
        expires_at: string | null;
        created_at: string;
    }>;
    usage: {
        today: number;
        this_month: number;
    };
    packages?: Array<any>;
}>();

const formatNumber = (val: number | string | null | undefined): string => {
    if (val === null || val === undefined || val === '') return 'Unlimited';
    const num = typeof val === 'string' ? parseInt(val, 10) : val;
    if (isNaN(num)) return 'Unlimited';
    return num.toLocaleString();
};

const tokens = ref(props.tokens);
const usage = ref(props.usage);
const packages = ref(props.packages || []);
const creating = ref(false);
const plainKey = ref<string | null>(null);
const copiedKey = ref<string | null>(null);
const showRegenModal = ref(false);
const regenSecretKey = ref('');

const showCheckoutModal = ref(false);
const pendingPayToken = ref<any>(null);
const paymentInfo = ref<any>(null);
const selectedPaymentMethod = ref('bank_transfer');
const processingPayment = ref(false);
const checkingPayment = ref(false);

const newToken = ref<{
    name: string;
    domain: string;
    package_id: number | null;
}>({
    name: '',
    domain: '',
    package_id: null,
});

const selectedPackageInfo = computed(() => {
    if (!newToken.value.package_id) {
        return packages.value.find(p => p.slug === 'free-developer') || null;
    }
    return packages.value.find(p => p.id === newToken.value.package_id) || null;
});

const selectPackageForNewKey = (packageId: number) => {
    newToken.value.package_id = packageId;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const createToken = async () => {
    creating.value = true;
    plainKey.value = null;

    try {
        const response = await axios.post('/account/api/tokens', newToken.value);
        if (response.data?.is_demo_restriction) {
            return;
        }

        // Refresh token list
        const listResponse = await axios.get('/account/api/tokens');
        tokens.value = listResponse.data.tokens;
        newToken.value = { name: '', domain: '', package_id: null };

        // Refresh usage
        const usageResponse = await axios.get('/account/api/usage');
        usage.value = usageResponse.data.usage;

        if (response.data.requires_payment) {
            // Paid plan requires checkout before key is active!
            pendingPayToken.value = response.data.token;
            paymentInfo.value = response.data.payment_info;
            showCheckoutModal.value = true;
        } else {
            // Free plan key created immediately
            plainKey.value = response.data.plain_key;
            dialog.success('Free Developer API key created successfully.');
        }
    } catch (err: any) {
        if (err.response?.data?.is_demo_restriction) {
            return;
        }
        dialog.error(err.response?.data?.message || 'Unable to create token.');
    } finally {
        creating.value = false;
    }
};

const openCheckoutForToken = async (token: any) => {
    pendingPayToken.value = token;
    showCheckoutModal.value = true;
    try {
        const res = await axios.post(`/account/api/tokens/${token.id}/check-payment`);
        if (res.data?.payment_info) {
            paymentInfo.value = res.data.payment_info;
        }
    } catch (e) {}
};

const checkPaymentStatus = async (tokenId: number) => {
    checkingPayment.value = true;
    try {
        const response = await axios.post(`/account/api/tokens/${tokenId}/check-payment`);
        if (response.data?.is_demo_restriction) {
            return;
        }

        if (response.data?.payment_info) {
            paymentInfo.value = response.data.payment_info;
        }

        if (response.data?.activated) {
            showCheckoutModal.value = false;
            plainKey.value = response.data.plain_key;

            // Refresh tokens list
            const listResponse = await axios.get('/account/api/tokens');
            tokens.value = listResponse.data.tokens;

            dialog.success(response.data.message || 'Payment confirmed! Your API key is now active.');
        } else {
            dialog.info(response.data?.message || 'Payment has not been confirmed yet.');
        }
    } catch (err: any) {
        if (err.response?.data?.is_demo_restriction) {
            return;
        }
        dialog.error(err.response?.data?.message || 'Unable to check payment status.');
    } finally {
        checkingPayment.value = false;
    }
};

const completePayment = async () => {
    if (!pendingPayToken.value) return;
    processingPayment.value = true;

    try {
        const response = await axios.post(`/account/api/tokens/${pendingPayToken.value.id}/pay`, {
            payment_method: selectedPaymentMethod.value,
        });

        if (response.data?.is_demo_restriction) {
            return;
        }

        showCheckoutModal.value = false;
        plainKey.value = response.data.plain_key;

        // Refresh tokens list
        const listResponse = await axios.get('/account/api/tokens');
        tokens.value = listResponse.data.tokens;

        dialog.success('Payment completed successfully! Your API key is now active.');
    } catch (err: any) {
        if (err.response?.data?.is_demo_restriction) {
            return;
        }
        dialog.error(err.response?.data?.message || 'Payment processing failed.');
    } finally {
        processingPayment.value = false;
    }
};

const regenerateToken = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Regenerate API Key',
        message: 'Are you sure you want to regenerate this API key secret? The old secret will stop working immediately.',
        confirmText: 'Regenerate',
        cancelText: 'Cancel',
        type: 'warning',
    });
    if (!confirmed) return;

    try {
        const response = await axios.post(`/account/api/tokens/${id}/regenerate`);
        if (response.data?.is_demo_restriction) {
            return;
        }
        regenSecretKey.value = response.data.plain_key;
        showRegenModal.value = true;
    } catch (err: any) {
        if (err.response?.data?.is_demo_restriction) {
            return;
        }
        dialog.error(err.response?.data?.message || 'Unable to regenerate token key.');
    }
};

const toggleToken = async (id: number) => {
    try {
        const response = await axios.post(`/account/api/tokens/${id}/toggle`);
        if (response.data?.is_demo_restriction) {
            return;
        }
        const target = tokens.value.find(t => t.id === id);
        if (target) {
            target.is_active = response.data.is_active;
        }
        dialog.success(response.data.message || 'Token status updated.');
    } catch (err: any) {
        if (err.response?.data?.is_demo_restriction) {
            return;
        }
        dialog.error(err.response?.data?.message || 'Unable to update token status.');
    }
};

const deleteToken = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Delete API Key',
        message: 'Are you sure you want to delete this API key? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });
    if (!confirmed) return;

    try {
        const response = await axios.delete(`/account/api/tokens/${id}`);
        if (response.data?.is_demo_restriction) {
            return;
        }
        tokens.value = tokens.value.filter(t => t.id !== id);
        dialog.success('API key deleted successfully.');
    } catch (err: any) {
        if (err.response?.data?.is_demo_restriction) {
            return;
        }
        dialog.error(err.response?.data?.message || 'Unable to delete token.');
    }
};

const copyText = (text: string | null) => {
    if (text) {
        navigator.clipboard.writeText(text);
        copiedKey.value = text;
        setTimeout(() => {
            copiedKey.value = null;
        }, 2500);
    }
};
</script>
