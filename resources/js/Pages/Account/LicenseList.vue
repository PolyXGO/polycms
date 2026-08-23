<template>
    <AccountLayout>
        <Head :title="t('My Licenses')" />
        <template #header>
            {{ t('My Licenses') }}
        </template>

        <!-- Claim External Purchase Section -->
        <div class="mb-6 bg-white dark:bg-[#111] p-5 rounded-2xl border border-gray-200 dark:border-zinc-800 transition-colors">
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 mb-2">
                <i class="fas fa-key text-indigo-500"></i>
                {{ t('Claim External Market Purchase') }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                {{ t('Purchased our products on Envato (or other marketplaces)? Enter your purchase code below to activate your lifetime license on this site.') }}
            </p>
            <form @submit.prevent="submitClaim" class="flex flex-col sm:flex-row gap-3">
                <div class="w-full sm:w-48">
                    <select 
                        v-model="claimForm.market" 
                        class="block w-full py-2.5 px-3 text-xs bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-gray-900 dark:text-zinc-100 transition-all shadow-sm"
                    >
                        <option v-for="platform in (market_platforms || [{name: 'Envato Market', code: 'envato'}])" :key="platform.code" :value="platform.code">
                            {{ platform.name }}
                        </option>
                    </select>
                </div>
                <div class="flex-1 relative">
                    <input 
                        type="text" 
                        v-model="claimForm.purchase_code" 
                        :placeholder="t('Enter Purchase Code (e.g. 8f6b15d2-a720-410a-...)')" 
                        required
                        class="block w-full py-2.5 px-3 text-xs bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-gray-900 dark:text-zinc-100 placeholder-gray-400 transition-all shadow-sm"
                    />
                </div>
                <button 
                    type="submit" 
                    :disabled="claimForm.processing"
                    class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <i v-if="claimForm.processing" class="fas fa-spinner animate-spin"></i>
                    {{ t('Claim License') }}
                </button>
            </form>
            <div v-if="claimForm.errors.purchase_code" class="mt-2 text-xs text-red-600">
                {{ claimForm.errors.purchase_code }}
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-4 flex items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 dark:text-zinc-500 text-xs"></i>
                </div>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    :placeholder="t('Search by product, license key, or order...')" 
                    class="block w-full pl-9 pr-3 py-2 text-xs bg-white dark:bg-[#111] border border-gray-200 dark:border-zinc-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:focus:ring-indigo-600 focus:border-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-600 transition-all shadow-sm"
                />
            </div>
            <button 
                v-if="searchQuery" 
                @click="searchQuery = ''" 
                class="text-xs text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
            >
                {{ t('Clear') }}
            </button>
        </div>

        <div class="bg-white dark:bg-[#111] shadow-sm overflow-x-auto sm:rounded-xl border border-gray-200 dark:border-zinc-800 transition-colors duration-300">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[220px] max-w-sm">
                            {{ t('Product') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('License Key') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Activations') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Status') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Expires At') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#111] divide-y divide-gray-200 dark:divide-zinc-800">
                    <template v-for="license in filteredLicenses" :key="license.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[220px] max-w-xs sm:max-w-sm md:max-w-md break-words">
                                <a v-if="license.subscription?.product?.slug" :href="getProductUrl(license.subscription.product.slug)" target="_blank" class="block font-semibold text-indigo-600 dark:text-indigo-400 hover:underline leading-snug break-words">
                                    {{ license.subscription?.product?.name || '-' }}
                                </a>
                                <span v-else class="block font-semibold text-indigo-600 dark:text-indigo-400 leading-snug break-words">
                                    {{ license.subscription?.product?.name || '-' }}
                                </span>
                                <Link v-if="license.order" :href="route('account.orders.show', license.order.code)" class="inline-flex items-center gap-1 mt-1 text-xs text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors font-normal">
                                    <i class="fas fa-shopping-bag text-[10px]"></i>
                                    {{ t('Order') }}: #{{ license.order.code }}
                                </Link>
                                <!-- Package / Plan Snapshot Info -->
                                <div v-if="getLicensePackageInfo(license)" class="mt-1">
                                    <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded text-[11px] font-medium break-all">
                                        <i class="fas fa-cube text-[9px] text-indigo-500 flex-shrink-0"></i>
                                        {{ getLicensePackageInfo(license) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-mono">
                                <div class="flex items-center gap-2">
                                    <span class="tracking-wider font-semibold text-xs">{{ formatMaskedKey(license.license_key, revealedKeys[license.id]) }}</span>
                                    <button 
                                        @click="toggleRevealKey(license.id)" 
                                        type="button" 
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1"
                                        :title="revealedKeys[license.id] ? t('Hide Key') : t('Show Key')"
                                    >
                                        <i class="fas text-xs" :class="revealedKeys[license.id] ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                    <button 
                                        @click="copyToClipboard(license.license_key)" 
                                        type="button" 
                                        class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1"
                                        :title="t('Copy Key')"
                                    >
                                        <i class="far text-xs" :class="copiedKey === license.license_key ? 'fa-check-circle text-green-500' : 'fa-copy'"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <button
                                    type="button"
                                    @click="openActivationsModal(license)"
                                    class="inline-flex items-center gap-1 font-semibold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
                                    title="Click to view & manage active domains"
                                >
                                    {{ license.activation_count }} / {{ license.max_activations }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': license.status === 'active',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': license.status === 'revoked',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': license.status === 'suspended'
                                    }">
                                    {{ t(license.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-medium">
                                <span :class="license.subscription?.expires_at ? '' : 'text-emerald-600 dark:text-emerald-400 font-bold'">
                                    {{ getExpiresAtText(license.subscription) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="openActivationsModal(license)" 
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 px-2.5 py-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors cursor-pointer"
                                        title="Manage active domains and devices"
                                    >
                                        <i class="fas fa-globe text-[10px]"></i>
                                        {{ t('Manage Domains') }}
                                    </button>
                                    <button 
                                        v-if="license.subscription?.product?.releases && license.subscription.product.releases.length > 0"
                                        @click="openDownloadsModal(license)" 
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold bg-gray-50 hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-zinc-700 transition-colors cursor-pointer"
                                    >
                                        <i class="fas fa-download"></i>
                                        {{ t('Downloads') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            
             <div v-if="filteredLicenses.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                {{ t('No licenses found.') }}
            </div>
        </div>

        <!-- Manage Customer Activations Modal -->
        <div v-if="showActivationsModal && activeLicense" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeActivationsModal"></div>

            <div class="bg-white dark:bg-[#151515] border border-gray-100 dark:border-zinc-800 rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden relative z-10 transition-all">
                <!-- Header -->
                <div class="p-5 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-gray-50/50 dark:bg-zinc-900/50">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <i class="fas fa-globe text-indigo-500"></i>
                            {{ t('Manage Active Domains & Devices') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">
                            {{ activeLicense.subscription?.product?.name }} ({{ activeLicense.activation_count }} / {{ activeLicense.max_activations }} slots used)
                        </p>
                    </div>
                    <button @click="closeActivationsModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ t('Below are the domains and devices currently registered to your license key. Deactivating a domain will free up a slot so you can activate on another domain.') }}
                    </p>

                    <div v-if="!activeLicense.activations || activeLicense.activations.length === 0" class="p-8 text-center border border-dashed border-gray-200 dark:border-zinc-800 rounded-xl text-xs text-gray-500 dark:text-gray-400 italic">
                        {{ t('No active domains registered yet for this license.') }}
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="act in activeLicense.activations"
                            :key="act.id"
                            class="p-4 border border-gray-200 dark:border-zinc-800 rounded-xl bg-gray-50/40 dark:bg-zinc-900/20 flex items-center justify-between gap-3 min-w-0"
                        >
                            <div class="min-w-0 flex-1 overflow-hidden">
                                <div class="text-sm font-semibold font-mono text-gray-900 dark:text-gray-100 truncate block" :title="act.domain || act.hwid || 'Unknown Domain'">
                                    {{ act.domain || act.hwid || 'Unknown Domain' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                    <span v-if="act.ip" class="font-mono">IP: {{ act.ip }}</span>
                                    <span>Activated: {{ formatDate(act.created_at) }}</span>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="deactivateDomain(act)"
                                :disabled="deactivatingId === act.id"
                                class="shrink-0 whitespace-nowrap px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/50 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors disabled:opacity-50"
                            >
                                {{ deactivatingId === act.id ? t('Deactivating...') : t('Deactivate') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-end">
                    <button @click="closeActivationsModal" type="button" class="px-4 py-2 text-xs font-bold bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 rounded-xl transition-all">
                        {{ t('Close') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Custom Deactivation Confirm Modal -->
        <div v-if="showConfirmModal && confirmTarget" class="fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4">
            <!-- Glassmorphism backdrop -->
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showConfirmModal = false"></div>

            <!-- Modal Content -->
            <div class="bg-white dark:bg-[#151515] border border-gray-100 dark:border-zinc-800 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden relative z-10 transition-all p-6 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center text-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                        {{ t('Deactivate License Domain') }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        {{ t('Are you sure you want to deactivate domain') }} <span class="font-mono font-bold text-gray-800 dark:text-gray-200 break-all">"{{ confirmTarget.domain || confirmTarget.hwid }}"</span>? {{ t('This will free up an activation slot for your license key.') }}
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button 
                        @click="showConfirmModal = false" 
                        type="button" 
                        class="px-4 py-2 text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 rounded-xl transition-all cursor-pointer"
                    >
                        {{ t('Cancel') }}
                    </button>
                    <button 
                        @click="executeDeactivation" 
                        type="button" 
                        class="px-4 py-2 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all cursor-pointer flex items-center gap-1.5 shadow-sm"
                    >
                        <i class="fas fa-trash-alt text-[10px]"></i>
                        {{ t('Deactivate Domain') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Downloads Modal -->
        <div v-if="showModal && activeLicense" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <!-- Glassmorphism backdrop -->
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

            <!-- Modal Content -->
            <div class="bg-white dark:bg-[#151515] border border-gray-100 dark:border-zinc-800 rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden relative z-10 transition-all transform scale-100">
                <!-- Header -->
                <div class="p-6 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-gray-50/50 dark:bg-zinc-900/50">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                            {{ activeLicense.subscription?.product?.name }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">
                            {{ t('License') }}: {{ activeLicense.license_key }}
                        </p>
                    </div>
                    <button @click="closeModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 max-h-[60vh] overflow-y-auto space-y-6">
                    <div class="text-xs font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 uppercase tracking-wider mb-2">
                        <i class="fas fa-history text-indigo-500"></i>
                        {{ t('Available Releases & Source Code') }}
                    </div>

                    <div v-if="activeLicense.subscription?.product?.releases && activeLicense.subscription.product.releases.length > 0" class="space-y-4">
                        <div v-for="release in activeLicense.subscription.product.releases" :key="'release-modal-' + release.id" class="p-4 border border-gray-100 dark:border-zinc-800 rounded-xl bg-gray-50/40 dark:bg-zinc-900/20 hover:border-indigo-100 dark:hover:border-indigo-900/40 transition-colors">
                            <div class="flex items-start justify-between gap-4 flex-wrap sm:flex-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900">
                                            {{ release.version }}
                                        </span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ release.title || t('Release') }}
                                        </span>
                                        <span v-if="release.is_prerelease" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-900">
                                            Pre-release
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ t('Released on') }}: {{ formatDate(release.released_at) }}
                                    </p>
                                    <p v-if="release.summary" class="text-xs text-gray-600 dark:text-gray-400 mt-2 whitespace-pre-line leading-relaxed">
                                        {{ release.summary }}
                                    </p>
                                </div>
                                <div class="flex sm:flex-col gap-2 shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                                    <template v-if="release.installer_windows_url || release.installer_macos_url">
                                        <a 
                                            v-if="release.installer_windows_url"
                                            :href="release.installer_windows_url" 
                                            target="_blank"
                                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap"
                                        >
                                            <i class="fab fa-windows text-[10px]"></i>
                                            {{ t('Download Windows') }}
                                        </a>
                                        <a 
                                            v-if="release.installer_macos_url"
                                            :href="release.installer_macos_url" 
                                            target="_blank"
                                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-zinc-700 hover:bg-zinc-800 text-white rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap"
                                        >
                                            <i class="fab fa-apple text-[10px]"></i>
                                            {{ t('Download macOS') }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        <a 
                                            v-if="release.download_url"
                                            :href="release.download_url" 
                                            target="_blank"
                                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap"
                                        >
                                            <i class="fas fa-file-archive text-[10px]"></i>
                                            {{ t('Download Paid') }}
                                        </a>
                                        <a 
                                            v-if="release.free_download_url"
                                            :href="release.free_download_url" 
                                            target="_blank"
                                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap"
                                        >
                                            <i class="fas fa-file-archive text-[10px]"></i>
                                            {{ t('Download Free') }}
                                        </a>
                                    </template>
                                    <span 
                                        v-if="release.download_expired" 
                                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400 border border-red-100 dark:border-red-900/30 rounded-lg whitespace-nowrap"
                                        :title="t('This update was released after your subscription expired. Please renew to access this version.')"
                                    >
                                        <i class="fas fa-exclamation-circle text-[10px]"></i>
                                        {{ t('Expired') }}
                                    </span>
                                    <span v-else-if="!release.download_url && !release.free_download_url" class="text-xs text-gray-400 dark:text-gray-500 italic text-center w-full">
                                        {{ t('No file link') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-gray-500 dark:text-gray-400 italic">
                        {{ t('No downloadable releases available for this product yet.') }}
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-end">
                    <button @click="closeModal" type="button" class="px-4 py-2 text-xs font-bold bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 rounded-xl transition-all cursor-pointer">
                        {{ t('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useTranslation } from '@/admin/composables/useTranslation';
import { useCurrency } from '@/Composables/useCurrency';

const { t } = useTranslation();
const { formatCurrency } = useCurrency();

const getExpiresAtText = (sub) => {
    if (!sub) return '-';
    if (!sub.expires_at) {
        return t('Lifetime / Never Expires');
    }
    const expDate = new Date(sub.expires_at);
    const now = new Date();
    if (expDate < now) {
        return t('Expired');
    }
    const diffTime = Math.abs(expDate.getTime() - now.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return `${expDate.toLocaleDateString()} (${diffDays} ${t('days remaining')})`;
};

const getLicensePackageInfo = (license) => {
    if (!license) return '';
    const item = license.order_item || license.subscription?.order_item;
    const sub = license.subscription;
    
    let planName = item?.metadata?.service_label || item?.metadata?.service_name || item?.service?.name || sub?.service?.name || item?.variant_label || '';
    let price = item?.price !== undefined && item?.price !== null ? item.price : (sub?.paid_price !== undefined && sub?.paid_price !== null ? sub.paid_price : null);
    
    if (!planName && (price === null || price === undefined)) return '';
    if (planName && price !== null && price !== undefined) {
        return `${planName} - ${formatCurrency(price)}`;
    }
    if (planName) return planName;
    if (price !== null && price !== undefined) return formatCurrency(price);
    return '';
};

const props = defineProps({
    licenses: Array,
    market_platforms: Array,
});

const claimForm = useForm({
    market: 'envato',
    purchase_code: '',
});

const submitClaim = () => {
    claimForm.post(route('api.v1.market.claim'), {
        preserveScroll: true,
        onSuccess: () => {
            claimForm.reset('purchase_code');
        },
    });
};

const searchQuery = ref('');
const showModal = ref(false);
const showActivationsModal = ref(false);
const showConfirmModal = ref(false);
const confirmTarget = ref(null);
const activeLicense = ref(null);
const copiedKey = ref('');
const deactivatingId = ref(null);
const revealedKeys = ref({});

const toggleRevealKey = (id) => {
    revealedKeys.value[id] = !revealedKeys.value[id];
};

const formatMaskedKey = (key, isRevealed) => {
    if (!key) return '';
    let normalized = key.replace(/^KEY-/, 'MTX-');
    if (isRevealed) return normalized;
    if (normalized.startsWith('MTX-')) {
        return 'MTX-••••-••••-••••';
    }
    return '••••-••••-••••-••••';
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return 'N/A';
        return d.toLocaleDateString();
    } catch (e) {
        return 'N/A';
    }
};

const copyToClipboard = (key) => {
    navigator.clipboard.writeText(key).then(() => {
        copiedKey.value = key;
        setTimeout(() => {
            if (copiedKey.value === key) {
                copiedKey.value = '';
            }
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
};

const getProductUrl = (slug) => {
    const pathParts = window.location.pathname.split('/');
    const activeLocales = ['vi', 'zh'];
    const currentLocale = pathParts[1];
    
    if (activeLocales.includes(currentLocale)) {
        return `/${currentLocale}/products/${slug}`;
    }
    return `/products/${slug}`;
};

const filteredLicenses = computed(() => {
    if (!searchQuery.value) {
        return props.licenses;
    }
    const q = searchQuery.value.toLowerCase().trim();
    return props.licenses.filter(license => {
        const productName = (license.subscription?.product?.name || '').toLowerCase();
        const licenseKey = (license.license_key || '').toLowerCase();
        const orderCode = (license.order?.code || '').toLowerCase();
        return productName.includes(q) || licenseKey.includes(q) || orderCode.includes(q);
    });
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const searchParam = params.get('search');
    if (searchParam) {
        searchQuery.value = searchParam;
    }
});

const openDownloadsModal = (license) => {
    activeLicense.value = license;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    activeLicense.value = null;
};

const openActivationsModal = (license) => {
    activeLicense.value = license;
    showActivationsModal.value = true;
};

const closeActivationsModal = () => {
    showActivationsModal.value = false;
    activeLicense.value = null;
};

const deactivateDomain = (act) => {
    if (!act || !act.id) return;
    confirmTarget.value = act;
    showConfirmModal.value = true;
};

const executeDeactivation = () => {
    const act = confirmTarget.value;
    if (!act || !act.id) return;

    showConfirmModal.value = false;
    deactivatingId.value = act.id;
    router.post(route('account.licenses.deactivate', act.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            deactivatingId.value = null;
            if (activeLicense.value) {
                // Update local activeLicense activations list
                activeLicense.value.activations = (activeLicense.value.activations || []).filter(a => a.id !== act.id);
                activeLicense.value.activation_count = activeLicense.value.activations.length;
            }
            confirmTarget.value = null;
        },
        onError: () => {
            deactivatingId.value = null;
            confirmTarget.value = null;
        }
    });
};
</script>
