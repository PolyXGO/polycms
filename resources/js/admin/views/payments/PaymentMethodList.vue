<template>
 <div class="payment-methods-page">
 <div class="mb-6">
 <div class="flex items-center gap-2">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Payment Methods') }}</h1>
 <HelpGuide 
 :title="t('Managing Payment Methods')" 
 :description="`<ul class='list-disc list-inside space-y-2 mt-2'><li>${t('Drag to reorder payment methods')}</li><li>${t('Click the star to set as default')}</li><li>${t('Click to expand and configure each method')}</li></ul>`"
 />
 </div>
 <p class="text-admin-theme-text-secondary mt-1">{{ t('Configure and manage payment gateways for your store.') }}</p>
 </div>

 <div v-if="loading" class="text-center py-12">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 </div>

 <div v-else-if="methods.length === 0" class="bg-admin-theme-surface rounded-lg shadow p-12 text-center">
 <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
 </svg>
 <h3 class="mt-4 text-lg font-medium text-admin-theme-text">{{ t('No Payment Methods') }}</h3>
 <p class="mt-2 text-admin-theme-text-muted">
 {{ t('Enable a payment gateway module to add payment methods.') }}
 </p>
 </div>

 <div v-else class="space-y-4">


 <!-- Filters and Search -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-4">
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div class="flex items-center gap-6">
 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary">
 <input v-model="filters.showActive" type="checkbox" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary" />
 <span>{{ t('Activate') }} ({{ stats.active }})</span>
 </label>
 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary">
 <input v-model="filters.showInactive" type="checkbox" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary" />
 <span>{{ t('Deactivate') }} ({{ stats.inactive }})</span>
 </label>
 </div>

 <div class="flex items-center gap-3 flex-1 md:max-w-md">
 <div class="relative flex-1">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <svg class="h-5 w-5 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
 </svg>
 </div>
 <input
 v-model="filters.search"
 type="text"
 :placeholder="t('Search...')"
 class="block w-full pl-10 pr-3 py-2 border border-admin-theme-border rounded-lg leading-5 bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:outline-none focus:ring-1 focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 />
 </div>

 <div class="flex items-center border border-admin-theme-border rounded-lg overflow-hidden">
 <button
 @click="viewMode ='grid'"
 :class="[
'p-2 transition-colors',
 viewMode ==='grid'
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'
 ]"
 :title="t('Grid View')"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
 </svg>
 </button>
 <button
 @click="viewMode ='list'"
 :class="[
'p-2 transition-colors border-l border-admin-theme-border',
 viewMode ==='list'
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'
 ]"
 :title="t('List View')"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
 </svg>
 </button>
 </div>
 </div>
 </div>
 </div>

 <div v-if="filteredMethods.length === 0" class="bg-admin-theme-surface rounded-lg shadow p-10 text-center text-admin-theme-text-muted">
 {{ t('No payment methods match your current filters.') }}
 </div>

 <!-- Payment Methods List -->
 <div 
 v-else-if="viewMode ==='list'"
 ref="listContainer"
 class="bg-admin-theme-surface rounded-lg shadow divide-y divide-admin-theme-border"
 >
 <div 
 v-for="(method, index) in listMethods" 
 :key="method.code"
 :data-code="method.code"
 class="payment-method-item transition-all duration-200"
 :class="{ 
'opacity-40 border-2 border-dashed border-indigo-400 dark:border-admin-theme-primary scale-[0.98]': canReorder && draggingIndex === index,
'bg-admin-theme-primary/10/50': canReorder && dragOverIndex === index
 }"
 @dragover="onMethodDragOver(index, $event)"
 @drop="onMethodDrop(index, $event)"
 >
 <!-- Header Row -->
 <div 
 class="flex items-center gap-4 p-4 cursor-pointer hover:bg-admin-theme-base"
 @click="toggleExpand(method.code)"
 :draggable="canReorder"
 @dragstart="onMethodDragStart(index, $event)"
 @dragend="onMethodDragEnd"
 >
 <!-- Drag Handle -->
 <div :class="[canReorder ?'cursor-move text-admin-theme-text-muted hover:text-admin-theme-text-muted' :'text-gray-300 dark:text-admin-theme-text-muted']">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
 </svg>
 </div>

 <!-- Default Star -->
 <button 
 @click.stop="setDefault(method.code)"
 class="p-1 rounded hover:bg-admin-theme-base"
 :class="method.is_default ?'text-yellow-500' :'text-gray-300 hover:text-yellow-400'"
 :title="method.is_default ? t('Default method') : t('Set as default')"
 >
 <svg class="w-5 h-5" :fill="method.is_default ?'currentColor' :'none'" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
 </svg>
 </button>

 <!-- Logo/Icon -->
 <div class="w-12 h-8 flex items-center justify-center bg-admin-theme-base rounded overflow-hidden">
 <img v-if="method.logo" :src="method.logo" :alt="method.name" class="max-w-full max-h-full object-contain" />
 <span v-else class="text-xs font-medium text-admin-theme-text-secondary uppercase">{{ method.code }}</span>
 </div>

 <!-- Name & Description -->
 <div class="flex-1 min-w-0">
 <h3 class="font-medium text-admin-theme-text">{{ method.name }}</h3>
 <p class="text-sm text-admin-theme-text-muted truncate">{{ method.description || t('No description') }}</p>
 </div>

 <!-- Status Toggle -->
 <div @click.stop class="flex items-center">
 <button 
 type="button"
 @click="toggleActive(method)"
 class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-admin-theme-primary focus:ring-offset-2"
 :class="method.is_active ?'bg-admin-theme-primary' :'bg-gray-200 dark:bg-gray-600'"
 >
 <span 
 class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
 :class="method.is_active ?'translate-x-5' :'translate-x-0'"
 ></span>
 </button>
 </div>

 <!-- Expand Arrow -->
 <svg 
 class="w-5 h-5 text-gray-400 transition-transform duration-200"
 :class="{'rotate-180': expandedMethod === method.code }"
 fill="none" stroke="currentColor" viewBox="0 0 24 24"
 >
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
 </svg>
 </div>

 <!-- Expanded Configuration -->
 <div 
 v-if="expandedMethod === method.code"
 class="border-t border-admin-theme-border bg-admin-theme-base p-6"
 >
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Basic Settings -->
 <div class="space-y-4">
 <h4 class="font-medium text-admin-theme-text">{{ t('Basic Settings') }}</h4>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Display Name') }}</label>
 <input 
 type="text" 
 v-model="editingConfig.name"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 />
 </div>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Description') }}</label>
 <textarea 
 v-model="editingConfig.description"
 rows="2"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 ></textarea>
 </div>

 <!-- Logo Picker -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Gateway Logo') }}</label>
 <div class="flex items-center gap-4">
 <div v-if="editingConfig.logo" class="flex-shrink-0">
 <img
 :src="editingConfig.logo"
 alt="Gateway Logo"
 class="h-12 w-auto object-contain border border-admin-theme-border rounded p-2 bg-admin-theme-input-bg"
 />
 </div>
 <div v-else class="flex-shrink-0 w-12 h-12 border-2 border-dashed border-admin-theme-border rounded flex items-center justify-center bg-admin-theme-base">
 <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 <div class="flex-1">
 <button
 type="button"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors text-sm"
 @click="openMediaPicker()"
 >
 {{ editingConfig.logo ? t('Change Logo') : t('Select Logo') }}
 </button>
 <button
 v-if="editingConfig.logo"
 type="button"
 class="ml-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-sm"
 @click="editingConfig.logo =''"
 >
 {{ t('Remove') }}
 </button>
 </div>
 </div>
 </div>
 </div>

 <!-- Gateway-specific Settings -->
 <div class="space-y-4">
   <div class="flex items-center gap-1.5">
  <h4 class="font-medium text-admin-theme-text">{{ t('Gateway Settings') }}</h4>
  <HelpGuide 
   v-if="method.code === 'paypal'"
   :title="t('PayPal Guide')"
   >
   <div class="space-y-4 leading-relaxed text-admin-theme-text-secondary max-h-[70vh] overflow-y-auto pr-1">
    <div>
     <h4 class="font-bold text-admin-theme-text mb-2 border-b border-admin-theme-border pb-1">{{ t('PayPal Sandbox Testing Guide') }}</h4>
     <ol class="list-decimal pl-5 space-y-1.5 text-sm">
      <li>
       {{ t('Go to the') }}
       <a href="https://developer.paypal.com/" target="_blank" class="text-admin-theme-primary hover:underline font-medium">
         {{ t('PayPal Developer Portal') }}
       </a>
       {{ t('and log in.') }}
      </li>
      <li>{{ t('Under Apps & Credentials, select Sandbox and click Create App to create a new application.') }}</li>
      <li>{{ t('Copy the Client ID and Client Secret, then enter them in the settings form.') }}</li>
      <li>{{ t('Enable the Sandbox Mode toggle in settings.') }}</li>
      <li>{{ t('Leave Webhook ID blank (bypasses signature validation for local testing).') }}</li>
      <li>{{ t('Test payments using a Sandbox Personal Buyer account (Testing > Sandbox Accounts on PayPal Dashboard).') }}</li>
     </ol>
    </div>

    <div>
     <h4 class="font-bold text-admin-theme-text mb-2 border-b border-admin-theme-border pb-1">{{ t('Webhook ID Guide') }}</h4>
     <div class="text-sm space-y-2">
      <p><strong>{{ t('Purpose:') }}</strong> {{ t('Used for asynchronous status notifications from PayPal (e.g. refunds, disputes, or delayed payments). PayPal will send event notifications to your webhook URL:') }}</p>
      <code class="block bg-admin-theme-surface p-2 rounded text-xs select-all break-all border border-admin-theme-border font-mono">{{ currentOrigin }}/api/v1/payment/paypal/webhook</code>
      <p><strong>{{ t('Use Case:') }}</strong> {{ t('Crucial for Live/Production environments to ensure payment updates are synchronized. For local development or Sandbox testing, you can leave it blank to bypass complex webhook signature verification.') }}</p>
     </div>
    </div>

    <div>
     <h4 class="font-bold text-admin-theme-text mb-2 border-b border-admin-theme-border pb-1">{{ t('Processing Fee (%)') }}</h4>
     <div class="text-sm space-y-1.5">
      <p><strong>{{ t('Purpose:') }}</strong> {{ t('An additional transaction fee charged to the customer at checkout, computed as a percentage of the total order value.') }}</p>
      <p><strong>{{ t('Example:') }}</strong> {{ t('If set to 3.5, a $100 order will have an extra $3.50 fee added to the checkout total. Set to 0 to disable.') }}</p>
     </div>
    </div>
   </div>
   </HelpGuide>
  </div>
 
  <!-- PayPal specific -->
  <template v-if="method.code ==='paypal'">
  <!-- Environment Tabs -->
  <div class="col-span-1 md:col-span-2 border-b border-admin-theme-border pb-2 flex gap-4">
    <button
      type="button"
      @click="activePaypalTab = 'sandbox'"
      class="pb-2 text-sm font-semibold border-b-2 transition-all focus:outline-none"
      :class="activePaypalTab === 'sandbox' ? 'border-admin-theme-primary text-admin-theme-text' : 'border-transparent text-admin-theme-text-muted hover:text-admin-theme-text'"
    >
      {{ t('Sandbox Mode') }}
    </button>
    <button
      type="button"
      @click="activePaypalTab = 'live'"
      class="pb-2 text-sm font-semibold border-b-2 transition-all focus:outline-none"
      :class="activePaypalTab === 'live' ? 'border-admin-theme-primary text-admin-theme-text' : 'border-transparent text-admin-theme-text-muted hover:text-admin-theme-text'"
    >
      {{ t('Live Mode') }}
    </button>
  </div>

  <!-- Active Environment Selector -->
  <div class="col-span-1 md:col-span-2 flex items-center justify-between bg-admin-theme-base/50 dark:bg-admin-theme-surface p-3 rounded-lg border border-admin-theme-border">
    <div class="flex flex-col">
      <span class="text-sm font-medium text-admin-theme-text">{{ t('Set Active Environment') }}</span>
      <span class="text-xs text-admin-theme-text-muted mt-0.5">
        {{ t('Currently active environment is:') }} 
        <span class="font-bold uppercase" :class="editingConfig.config.mode === 'live' ? 'text-green-600 dark:text-green-400' : 'text-indigo-600 dark:text-indigo-400'">
          {{ editingConfig.config.mode }}
        </span>
      </span>
    </div>
    <button 
      type="button"
      @click="editingConfig.config.mode = activePaypalTab"
      class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-admin-theme-primary focus:ring-offset-2"
      :class="editingConfig.config.mode === activePaypalTab ? 'bg-admin-theme-primary' : 'bg-gray-200 dark:bg-gray-600'"
    >
      <span 
        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
        :class="editingConfig.config.mode === activePaypalTab ? 'translate-x-5' : 'translate-x-0'"
      ></span>
    </button>
  </div>

  <!-- Sandbox Tab Content -->
  <template v-if="activePaypalTab === 'sandbox'">
    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Sandbox Client ID') }}</label>
      <input 
        type="text" 
        v-model="editingConfig.config.sandbox_client_id"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
      />
    </div>
    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Sandbox Client Secret') }}</label>
      <input 
        type="password" 
        v-model="editingConfig.config.sandbox_client_secret"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
      />
    </div>
    <div class="col-span-1 md:col-span-2">
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Sandbox Webhook ID') }}</label>
      <input 
        type="text" 
        v-model="editingConfig.config.sandbox_webhook_id"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm mb-2"
      />
      <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-900/30 rounded-lg p-3">
        <label class="block text-xs font-semibold text-blue-800 dark:text-blue-400 uppercase mb-1">{{ t('Sandbox Webhook URL') }}</label>
        <div class="flex">
          <code class="flex-1 bg-admin-theme-surface border border-blue-200 dark:border-blue-800 rounded-l px-3 py-1.5 text-xs font-mono text-admin-theme-text select-all break-all">
            {{ currentOrigin }}/api/v1/payment/paypal/webhook
          </code>
          <button 
            type="button"
            @click="copyToClipboard(currentOrigin + '/api/v1/payment/paypal/webhook')"
            class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1.5 rounded-r border-t border-r border-b border-blue-200 dark:border-blue-800 hover:bg-blue-200 dark:hover:bg-blue-900/50 font-medium text-xs shrink-0"
          >
            {{ t('Copy') }}
          </button>
        </div>
      </div>
    </div>
  </template>

  <!-- Live Tab Content -->
  <template v-if="activePaypalTab === 'live'">
    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Live Client ID') }}</label>
      <input 
        type="text" 
        v-model="editingConfig.config.live_client_id"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
      />
    </div>
    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Live Client Secret') }}</label>
      <input 
        type="password" 
        v-model="editingConfig.config.live_client_secret"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
      />
    </div>
    <div class="col-span-1 md:col-span-2">
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Live Webhook ID') }}</label>
      <input 
        type="text" 
        v-model="editingConfig.config.live_webhook_id"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm mb-2"
      />
      <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-900/30 rounded-lg p-3">
        <label class="block text-xs font-semibold text-blue-800 dark:text-blue-400 uppercase mb-1">{{ t('Live Webhook URL') }}</label>
        <div class="flex">
          <code class="flex-1 bg-admin-theme-surface border border-blue-200 dark:border-blue-800 rounded-l px-3 py-1.5 text-xs font-mono text-admin-theme-text select-all break-all">
            {{ currentOrigin }}/api/v1/payment/paypal/webhook
          </code>
          <button 
            type="button"
            @click="copyToClipboard(currentOrigin + '/api/v1/payment/paypal/webhook')"
            class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1.5 rounded-r border-t border-r border-b border-blue-200 dark:border-blue-800 hover:bg-blue-200 dark:hover:bg-blue-900/50 font-medium text-xs shrink-0"
          >
            {{ t('Copy') }}
          </button>
        </div>
      </div>
    </div>
  </template>

  <!-- Connection Test -->
  <div class="col-span-1 md:col-span-2 mt-2">
    <button
      type="button"
      @click="testPaypalConnection"
      :disabled="testingPaypal || (activePaypalTab === 'live' ? (!editingConfig.config.live_client_id || !editingConfig.config.live_client_secret) : (!editingConfig.config.sandbox_client_id || !editingConfig.config.sandbox_client_secret))"
      class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2"
    >
      <svg v-if="testingPaypal" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      {{ testingPaypal ? t('Testing...') : t('Test Connection') }}
    </button>
    <p v-if="paypalTestResult" class="mt-2 text-sm font-medium" :class="paypalTestResult.success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
      {{ paypalTestResult.message }}
    </p>
  </div>
  </template>

 <!-- Stripe specific -->
 <template v-else-if="method.code ==='stripe'">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Publishable Key') }}</label>
 <input 
 type="text" 
 v-model="editingConfig.config.publishable_key"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
 />
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Secret Key') }}</label>
 <input 
 type="password" 
 v-model="editingConfig.config.secret_key"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
 />
 </div>
 </template>

 <!-- SePay specific -->
 <template v-else-if="method.code ==='sepay'">
 <!-- Webhook Configuration Info -->
 <div class="col-span-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
 <h5 class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-2 flex items-center">
 <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
 {{ t('Webhook Configuration') }}
 </h5>
 <p class="text-sm text-blue-700 dark:text-blue-400 mb-3">
 {{ t('To receive payment confirmations, please set up the Webhook in your SePay Dashboard:') }}
 </p>
 <div class="space-y-3">
 <div>
 <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300 uppercase mb-1">{{ t('Webhook URL') }}</label>
 <div class="flex">
 <code class="flex-1 bg-admin-theme-surface border border-blue-200 dark:border-blue-700 rounded-l px-3 py-2 text-sm font-mono text-admin-theme-text select-all">
 {{ currentOrigin }}/api/webhooks/sepay
 </code>
 <button 
 type="button"
 @click="copyToClipboard(currentOrigin +'/api/webhooks/sepay')"
 class="bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 px-3 py-2 rounded-r border-t border-r border-b border-blue-200 dark:border-blue-700 hover:bg-blue-200 dark:hover:bg-blue-700 font-medium text-sm"
 >
 {{ t('Copy') }}
 </button>
 </div>
 </div>
 <div>
 <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300 uppercase mb-1">{{ t('Events to Subscribe') }}</label>
 <div class="flex items-center gap-2">
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
 Incoming Transfer
 </span>
 </div>
 </div>
 </div>
 </div>

 <!-- Bank Accounts -->
 <div class="col-span-2 space-y-4">
 <div class="flex items-center justify-between">
 <h5 class="font-medium text-admin-theme-text">{{ t('Bank Accounts') }}</h5>
 <button 
 type="button"
 @click="addSepayBank"
 class="px-3 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content text-sm rounded hover:bg-admin-theme-primary-hover"
 >
 + {{ t('Add Bank') }}
 </button>
 </div>
 
 <div v-if="!editingConfig.config.banks || editingConfig.config.banks.length === 0" 
 class="text-sm text-admin-theme-text-muted italic">
 {{ t('No bank accounts configured. Add a bank account to enable SePay payments.') }}
 </div>
 
 <div v-for="(bank, idx) in editingConfig.config.banks" :key="idx" 
 class="border border-admin-theme-border rounded-lg p-4 space-y-3">
 <div class="grid grid-cols-2 gap-3">
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Bank Code') }} *</label>
 <input 
 type="text" 
 v-model="bank.bank_code"
 placeholder="ACB, VCB, BIDV, MB..."
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Bank Name') }}</label>
 <input 
 type="text" 
 v-model="bank.bank_name"
 placeholder="Asia Commercial Bank"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Number') }} *</label>
 <input 
 type="text" 
 v-model="bank.account_number"
 placeholder="7794181"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm font-mono"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Holder Name') }} *</label>
 <input 
 type="text" 
 v-model="bank.account_holder"
 placeholder="NGUYEN VAN A"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 </div>
 
 <!-- QR Code Preview Section -->
 <div class="pt-3 border-t border-admin-theme-border">
 <div class="flex items-start gap-4">
 <div class="flex-1">
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Preview Amount (VND)') }}</label>
 <div class="flex gap-2">
 <input 
 type="number" 
 v-model.number="bank.preview_amount"
 placeholder="100000"
 class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 <button 
 type="button"
 @click="generateQrPreview(Number(idx))"
 :disabled="!bank.bank_code || !bank.account_number"
 class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
 >
 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
 </svg>
 {{ t('Generate QR') }}
 </button>
 </div>
 </div>
 
 <!-- QR Code Display -->
 <div v-if="bank.qr_preview_url" class="w-full flex justify-center">
 <div class="inline-flex flex-col items-center bg-white p-3 rounded-lg border border-gray-200 shadow-sm max-w-full">
 <img 
 :src="bank.qr_preview_url" 
 :alt="'QR Code for' + bank.bank_code"
 class="w-36 h-36 object-contain"
 @error="bank.qr_preview_url =''"
 />
 <p class="text-xs text-center text-gray-500 mt-2">
 {{ formatVND(bank.preview_amount || 0) }}
 </p>
 </div>
 </div>
 </div>
 </div>
 
 <div class="flex items-center justify-between pt-2 border-t border-admin-theme-border">
 <label class="flex items-center gap-2 cursor-pointer">
 <input 
 type="radio" 
 :name="'sepay_primary_' + method.code"
 :checked="bank.is_primary"
 @change="setSepayPrimaryBank(Number(idx))"
 class="text-admin-theme-primary"
 />
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Primary Bank') }}</span>
 </label>
 <button 
 type="button"
 @click="removeSepayBank(Number(idx))"
 class="text-red-500 hover:text-red-700 text-sm"
 >
 {{ t('Remove') }}
 </button>
 </div>
 </div>
 </div>
 
 <!-- API Key -->
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('SEPAY API Key') }}</label>
 <input 
 type="text" 
 v-model="editingConfig.config.api_key"
 placeholder="QFVCWSQDWV6Q1ALMWKBTIBYR3RHTFS2XE85PBY3OAOHVIIL..."
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
 />
 <p class="mt-1 text-xs text-admin-theme-text-muted">
 {{ t('API Key for webhook authentication from SEPAY. If left empty, webhook will not check authentication.') }}
 <br/>
 {{ t('Note: Header will be:') }} <code class="text-admin-theme-primary dark:text-admin-theme-primary">Authorization: Apikey YOUR_API_KEY</code>
 </p>
 </div>
 </template>



 <!-- COD specific -->
 <template v-else-if="method.code ==='cod'">
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Instructions') }}</label>
 <textarea 
 v-model="editingConfig.config.instructions"
 rows="3"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 :placeholder="t('Instructions for customers...')"
 ></textarea>
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Additional Fee') }}</label>
 <input 
 type="number" 
 v-model.number="editingConfig.config.additional_fee"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Fee Type') }}</label>
 <select 
 v-model="editingConfig.config.fee_type"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 >
 <option value="fixed">{{ t('Fixed Amount') }}</option>
 <option value="percentage">{{ t('Percentage') }}</option>
 </select>
 </div>
 </template>

 <!-- Bank Transfer specific -->
 <template v-else-if="method.code ==='bank_transfer'">
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Instructions') }}</label>
 <textarea 
 v-model="editingConfig.config.instructions"
 rows="3"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 :placeholder="t('Instructions for customers...')"
 ></textarea>
 </div>
 
 <!-- Bank Accounts (Reuse SePay logic/structure but tailored for manual transfer if needed) -->
 <div class="col-span-2 space-y-4">
 <div class="flex items-center justify-between">
 <h5 class="font-medium text-admin-theme-text">{{ t('Bank Accounts') }}</h5>
 <button 
 type="button"
 @click="addBankTransferAccount"
 class="px-3 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content text-sm rounded hover:bg-admin-theme-primary-hover"
 >
 + {{ t('Add Bank') }}
 </button>
 </div>
 
 <div v-if="!editingConfig.config.banks || editingConfig.config.banks.length === 0" 
 class="text-sm text-admin-theme-text-muted italic">
 {{ t('No bank accounts configured.') }}
 </div>
 
 <div v-for="(bank, idx) in editingConfig.config.banks" :key="idx" 
 class="bank-item border border-admin-theme-border rounded-lg p-4 space-y-3 transition-all duration-200"
 :class="{ 
'opacity-40 border-2 border-dashed border-indigo-400 dark:border-admin-theme-primary scale-[0.98]': draggingBankIndex === idx, 
'bg-admin-theme-primary/10/50 border-admin-theme-primary ring-1 ring-indigo-500': dragOverBankIndex === idx 
 }"
 @dragover="handleBankDragOver(Number(idx), $event)"
 @drop="handleBankDrop(Number(idx), $event)"
 >
 
 <!-- Bank Logo & Handle -->
 <div class="flex items-center gap-4">
 <!-- Drag Handle -->
 <div 
 class="cursor-move text-gray-400 hover:text-gray-600"
 draggable="true"
 @dragstart="handleBankDragStart(Number(idx), $event)"
 @dragend="handleBankDragEnd"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
 </svg>
 </div>

 <div class="w-16 h-16 border rounded flex items-center justify-center bg-admin-theme-base cursor-pointer overflow-hidden flex-shrink-0"
 @click="selectBankLogo(idx)"
 :title="t('Click to change logo')">
 <img v-if="bank.logo" :src="bank.logo" class="w-full h-full object-contain" />
 <span v-else class="text-xs text-center text-gray-400">Logo</span>
 </div>
 <div class="flex-1 grid grid-cols-2 gap-3">
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Bank Name') }}</label>
 <input 
 type="text" 
 v-model="bank.bank_name"
 placeholder="Asia Commercial Bank"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Number') }}</label>
 <input 
 type="text" 
 v-model="bank.account_number"
 placeholder="7794181"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm font-mono"
 />
 </div>
 <div class="col-span-2">
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Holder Name') }}</label>
 <input 
 type="text" 
 v-model="bank.account_holder"
 placeholder="NGUYEN VAN A"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 </div>
 </div>

 <div class="flex items-center justify-end pt-2 border-t border-admin-theme-border">
 <button 
 type="button"
 @click="removeBankTransferAccount(Number(idx))"
 class="text-red-500 hover:text-red-700 text-sm"
 >
 {{ t('Remove') }}
 </button>
 </div>
 </div>
 </div>
 </template>

 <!-- Generic settings for other gateways -->
 <template v-else>
 <div class="text-sm text-admin-theme-text-muted">
 {{ t('Configure this gateway in the module settings.') }}
 </div>
 </template>

 <!-- Processing Fee (common) -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Processing Fee (%)') }}</label>
 <FormInput
 :name="`processing_fee_${method.code}`"
 :model-value="editingConfig.config.processing_fee ??''"
 type="text"
 inputmode="decimal"
 :numeric-only="true"
 :allow-decimal="true"
 :rules="[{ type:'numeric', message: t('Processing fee must be a valid number') }]"
 validate-on="blur"
 :show-error="true"
 @update:model-value="handleProcessingFeeInput"
 />
 </div>
 </div>
 </div>

 <!-- Save Button -->
 <div class="mt-6 flex justify-end gap-3">
 <button 
 @click="expandedMethod = null"
 class="px-4 py-2 text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-base"
 >
 {{ t('Cancel') }}
 </button>
 <button 
 @click="saveConfig(method)"
 :disabled="saving"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50"
 >
 {{ saving ? t('Saving...') : t('Save Changes') }}
 </button>
 </div>
 </div>
 </div>
 </div>

 <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
 <div
 v-for="(method, index) in filteredMethods"
 :key="method.code"
 class="bg-admin-theme-surface rounded-lg shadow hover:shadow-lg transition-all duration-200"
 :class="{
'opacity-40 border-2 border-dashed border-indigo-400 dark:border-admin-theme-primary scale-[0.98]': canReorder && draggingIndex === index,
'ring-2 ring-indigo-500/60': canReorder && dragOverIndex === index
 }"
 @dragover="onMethodDragOver(index, $event)"
 @drop="onMethodDrop(index, $event)"
 >
 <div class="p-6">
 <div class="flex items-start justify-between mb-4">
 <div class="flex items-center gap-3 min-w-0">
 <div
 :class="[
 canReorder ?'cursor-move text-gray-400 hover:text-gray-600' :'text-gray-300 dark:text-admin-theme-text-muted'
 ]"
 :draggable="canReorder"
 @dragstart="onMethodDragStart(index, $event)"
 @dragend="onMethodDragEnd"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
 </svg>
 </div>
 <div class="w-12 h-8 flex items-center justify-center bg-admin-theme-base rounded overflow-hidden">
 <img v-if="method.logo" :src="method.logo" :alt="method.name" class="max-w-full max-h-full object-contain" />
 <span v-else class="text-xs font-medium text-admin-theme-text-secondary uppercase">{{ method.code }}</span>
 </div>
 <div class="min-w-0">
 <h3 class="text-lg font-semibold text-admin-theme-text truncate">{{ method.name }}</h3>
 <p class="text-xs text-admin-theme-text-muted">{{ method.code }}</p>
 </div>
 </div>
 <span
 :class="[
'px-2 py-1 text-xs font-semibold rounded-full',
 method.is_active
 ?'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
 :'bg-admin-theme-base text-admin-theme-text'
 ]"
 >
 {{ method.is_active ? t('Active') : t('Inactive') }}
 </span>
 </div>

 <p class="text-sm text-admin-theme-text-secondary mb-4 line-clamp-2">
 {{ method.description || t('No description') }}
 </p>

 <div class="pt-4 border-t border-admin-theme-border space-y-3">
 <div class="flex items-center justify-between">
 <button
 @click="setDefault(method.code)"
 class="inline-flex items-center gap-2 text-sm"
 :class="method.is_default ?'text-yellow-500' :'text-admin-theme-text-muted hover:text-yellow-500'"
 >
 <svg class="w-5 h-5" :fill="method.is_default ?'currentColor' :'none'" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
 </svg>
 <span>{{ method.is_default ? t('Default method') : t('Set as default') }}</span>
 </button>
 <button 
 type="button"
 @click="toggleActive(method)"
 class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-admin-theme-primary focus:ring-offset-2"
 :class="method.is_active ?'bg-admin-theme-primary' :'bg-gray-200 dark:bg-gray-600'"
 >
 <span 
 class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
 :class="method.is_active ?'translate-x-5' :'translate-x-0'"
 ></span>
 </button>
 </div>

 <button
 type="button"
 @click="openGridModal(method)"
 class="w-full px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 {{ t('Configure') }}
 </button>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div
 v-if="gridModalOpen && gridModalMethod"
 class="fixed inset-0 z-[80] bg-black/60 flex items-center justify-center p-4"
 @click.self="closeGridModal"
 >
 <div class="w-full max-w-4xl max-h-[90vh] overflow-auto overflow-x-hidden bg-admin-theme-surface rounded-lg shadow-xl">
 <div class="px-6 py-4 border-b border-admin-theme-border flex items-center justify-between">
 <h3 class="text-lg font-semibold text-admin-theme-text">
 {{ t('Configure') }} {{ gridModalMethod.name }}
 </h3>
 <button @click="closeGridModal" class="text-admin-theme-text-muted hover:text-admin-theme-text">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
 </svg>
 </button>
 </div>

 <div class="p-6">
 <div class="space-y-6">
 <!-- Basic Settings -->
 <div class="space-y-4">
 <h4 class="font-medium text-admin-theme-text">{{ t('Basic Settings') }}</h4>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Display Name') }}</label>
 <input 
 type="text" 
 v-model="editingConfig.name"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 />
 </div>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Description') }}</label>
 <textarea 
 v-model="editingConfig.description"
 rows="2"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 ></textarea>
 </div>

 <!-- Logo Picker -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Gateway Logo') }}</label>
 <div class="flex items-center gap-4">
 <div v-if="editingConfig.logo" class="flex-shrink-0">
 <img
 :src="editingConfig.logo"
 alt="Gateway Logo"
 class="h-12 w-auto object-contain border border-admin-theme-border rounded p-2 bg-admin-theme-input-bg"
 />
 </div>
 <div v-else class="flex-shrink-0 w-12 h-12 border-2 border-dashed border-admin-theme-border rounded flex items-center justify-center bg-admin-theme-base">
 <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 <div class="flex-1">
 <button
 type="button"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors text-sm"
 @click="openMediaPicker()"
 >
 {{ editingConfig.logo ? t('Change Logo') : t('Select Logo') }}
 </button>
 <button
 v-if="editingConfig.logo"
 type="button"
 class="ml-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-sm"
 @click="editingConfig.logo =''"
 >
 {{ t('Remove') }}
 </button>
 </div>
 </div>
 </div>
 </div>

 <!-- Gateway-specific Settings -->
 <div class="space-y-4">
   <div class="flex items-center gap-1.5">
  <h4 class="font-medium text-admin-theme-text">{{ t('Gateway Settings') }}</h4>
  <HelpGuide 
  v-if="gridModalMethod.code === 'paypal'"
  :title="t('PayPal Guide')"
  >
  <div class="space-y-4 leading-relaxed text-admin-theme-text-secondary max-h-[70vh] overflow-y-auto pr-1">
   <div>
    <h4 class="font-bold text-admin-theme-text mb-2 border-b border-admin-theme-border pb-1">{{ t('PayPal Sandbox Testing Guide') }}</h4>
    <ol class="list-decimal pl-5 space-y-1.5 text-sm">
     <li>
      {{ t('Go to the') }}
      <a href="https://developer.paypal.com/" target="_blank" class="text-admin-theme-primary hover:underline font-medium">
        {{ t('PayPal Developer Portal') }}
      </a>
      {{ t('and log in.') }}
     </li>
     <li>{{ t('Under Apps & Credentials, select Sandbox and click Create App to create a new application.') }}</li>
     <li>{{ t('Copy the Client ID and Client Secret, then enter them in the settings form.') }}</li>
     <li>{{ t('Enable the Sandbox Mode toggle in settings.') }}</li>
     <li>{{ t('Leave Webhook ID blank (bypasses signature validation for local testing).') }}</li>
     <li>{{ t('Test payments using a Sandbox Personal Buyer account (Testing > Sandbox Accounts on PayPal Dashboard).') }}</li>
    </ol>
   </div>

   <div>
    <h4 class="font-bold text-admin-theme-text mb-2 border-b border-admin-theme-border pb-1">{{ t('Webhook ID Guide') }}</h4>
    <div class="text-sm space-y-2">
     <p><strong>{{ t('Purpose:') }}</strong> {{ t('Used for asynchronous status notifications from PayPal (e.g. refunds, disputes, or delayed payments). PayPal will send event notifications to your webhook URL:') }}</p>
     <code class="block bg-admin-theme-surface p-2 rounded text-xs select-all break-all border border-admin-theme-border font-mono">{{ currentOrigin }}/api/v1/payment/paypal/webhook</code>
     <p><strong>{{ t('Use Case:') }}</strong> {{ t('Crucial for Live/Production environments to ensure payment updates are synchronized. For local development or Sandbox testing, you can leave it blank to bypass complex webhook signature verification.') }}</p>
    </div>
   </div>

   <div>
    <h4 class="font-bold text-admin-theme-text mb-2 border-b border-admin-theme-border pb-1">{{ t('Processing Fee (%)') }}</h4>
    <div class="text-sm space-y-1.5">
     <p><strong>{{ t('Purpose:') }}</strong> {{ t('An additional transaction fee charged to the customer at checkout, computed as a percentage of the total order value.') }}</p>
     <p><strong>{{ t('Example:') }}</strong> {{ t('If set to 3.5, a $100 order will have an extra $3.50 fee added to the checkout total. Set to 0 to disable.') }}</p>
    </div>
   </div>
  </div>
  </HelpGuide>
  </div>
 
 <!-- PayPal specific -->
 <template v-if="gridModalMethod.code ==='paypal'">
 <!-- Environment Tabs -->
 <div class="col-span-1 md:col-span-2 border-b border-admin-theme-border pb-2 flex gap-4">
   <button
     type="button"
     @click="activePaypalTab = 'sandbox'"
     class="pb-2 text-sm font-semibold border-b-2 transition-all focus:outline-none"
     :class="activePaypalTab === 'sandbox' ? 'border-admin-theme-primary text-admin-theme-text' : 'border-transparent text-admin-theme-text-muted hover:text-admin-theme-text'"
   >
     {{ t('Sandbox Mode') }}
   </button>
   <button
     type="button"
     @click="activePaypalTab = 'live'"
     class="pb-2 text-sm font-semibold border-b-2 transition-all focus:outline-none"
     :class="activePaypalTab === 'live' ? 'border-admin-theme-primary text-admin-theme-text' : 'border-transparent text-admin-theme-text-muted hover:text-admin-theme-text'"
   >
     {{ t('Live Mode') }}
   </button>
 </div>

 <!-- Active Environment Selector -->
 <div class="col-span-1 md:col-span-2 flex items-center justify-between bg-admin-theme-base/50 dark:bg-admin-theme-surface p-3 rounded-lg border border-admin-theme-border">
   <div class="flex flex-col">
     <span class="text-sm font-medium text-admin-theme-text">{{ t('Set Active Environment') }}</span>
     <span class="text-xs text-admin-theme-text-muted mt-0.5">
       {{ t('Currently active environment is:') }} 
       <span class="font-bold uppercase" :class="editingConfig.config.mode === 'live' ? 'text-green-600 dark:text-green-400' : 'text-indigo-600 dark:text-indigo-400'">
         {{ editingConfig.config.mode }}
       </span>
     </span>
   </div>
   <button 
     type="button"
     @click="editingConfig.config.mode = activePaypalTab"
     class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-admin-theme-primary focus:ring-offset-2"
     :class="editingConfig.config.mode === activePaypalTab ? 'bg-admin-theme-primary' : 'bg-gray-200 dark:bg-gray-600'"
   >
     <span 
       class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
       :class="editingConfig.config.mode === activePaypalTab ? 'translate-x-5' : 'translate-x-0'"
     ></span>
   </button>
 </div>

 <!-- Sandbox Tab Content -->
 <template v-if="activePaypalTab === 'sandbox'">
   <div>
     <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Sandbox Client ID') }}</label>
     <input 
       type="text" 
       v-model="editingConfig.config.sandbox_client_id"
       class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
     />
   </div>
   <div>
     <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Sandbox Client Secret') }}</label>
     <input 
       type="password" 
       v-model="editingConfig.config.sandbox_client_secret"
       class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
     />
   </div>
   <div class="col-span-1 md:col-span-2">
     <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Sandbox Webhook ID') }}</label>
     <input 
       type="text" 
       v-model="editingConfig.config.sandbox_webhook_id"
       class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm mb-2"
     />
     <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-900/30 rounded-lg p-3">
       <label class="block text-xs font-semibold text-blue-800 dark:text-blue-400 uppercase mb-1">{{ t('Sandbox Webhook URL') }}</label>
       <div class="flex">
         <code class="flex-1 bg-admin-theme-surface border border-blue-200 dark:border-blue-800 rounded-l px-3 py-1.5 text-xs font-mono text-admin-theme-text select-all break-all">
           {{ currentOrigin }}/api/v1/payment/paypal/webhook
         </code>
         <button 
           type="button"
           @click="copyToClipboard(currentOrigin + '/api/v1/payment/paypal/webhook')"
           class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1.5 rounded-r border-t border-r border-b border-blue-200 dark:border-blue-800 hover:bg-blue-200 dark:hover:bg-blue-900/50 font-medium text-xs shrink-0"
         >
           {{ t('Copy') }}
         </button>
       </div>
     </div>
   </div>
 </template>

 <!-- Live Tab Content -->
 <template v-if="activePaypalTab === 'live'">
   <div>
     <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Live Client ID') }}</label>
     <input 
       type="text" 
       v-model="editingConfig.config.live_client_id"
       class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
     />
   </div>
   <div>
     <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Live Client Secret') }}</label>
     <input 
       type="password" 
       v-model="editingConfig.config.live_client_secret"
       class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
     />
   </div>
   <div class="col-span-1 md:col-span-2">
     <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Live Webhook ID') }}</label>
     <input 
       type="text" 
       v-model="editingConfig.config.live_webhook_id"
       class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm mb-2"
     />
     <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-900/30 rounded-lg p-3">
       <label class="block text-xs font-semibold text-blue-800 dark:text-blue-400 uppercase mb-1">{{ t('Live Webhook URL') }}</label>
       <div class="flex">
         <code class="flex-1 bg-admin-theme-surface border border-blue-200 dark:border-blue-800 rounded-l px-3 py-1.5 text-xs font-mono text-admin-theme-text select-all break-all">
           {{ currentOrigin }}/api/v1/payment/paypal/webhook
         </code>
         <button 
           type="button"
           @click="copyToClipboard(currentOrigin + '/api/v1/payment/paypal/webhook')"
           class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1.5 rounded-r border-t border-r border-b border-blue-200 dark:border-blue-800 hover:bg-blue-200 dark:hover:bg-blue-900/50 font-medium text-xs shrink-0"
         >
           {{ t('Copy') }}
         </button>
       </div>
     </div>
   </div>
 </template>

 <!-- Connection Test -->
 <div class="col-span-1 md:col-span-2 mt-2">
   <button
     type="button"
     @click="testPaypalConnection"
     :disabled="testingPaypal || (activePaypalTab === 'live' ? (!editingConfig.config.live_client_id || !editingConfig.config.live_client_secret) : (!editingConfig.config.sandbox_client_id || !editingConfig.config.sandbox_client_secret))"
     class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2"
   >
     <svg v-if="testingPaypal" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
       <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
       <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
     </svg>
     {{ testingPaypal ? t('Testing...') : t('Test Connection') }}
   </button>
   <p v-if="paypalTestResult" class="mt-2 text-sm font-medium" :class="paypalTestResult.success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
     {{ paypalTestResult.message }}
   </p>
 </div>
 </template>

 <!-- Stripe specific -->
 <template v-else-if="gridModalMethod.code ==='stripe'">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Publishable Key') }}</label>
 <input 
 type="text" 
 v-model="editingConfig.config.publishable_key"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
 />
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Secret Key') }}</label>
 <input 
 type="password" 
 v-model="editingConfig.config.secret_key"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
 />
 </div>
 </template>

 <!-- SePay specific -->
 <template v-else-if="gridModalMethod.code ==='sepay'">
 <!-- Webhook Configuration Info -->
 <div class="col-span-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
 <h5 class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-2 flex items-center">
 <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
 {{ t('Webhook Configuration') }}
 </h5>
 <p class="text-sm text-blue-700 dark:text-blue-400 mb-3">
 {{ t('To receive payment confirmations, please set up the Webhook in your SePay Dashboard:') }}
 </p>
 <div class="space-y-3">
 <div>
 <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300 uppercase mb-1">{{ t('Webhook URL') }}</label>
 <div class="flex min-w-0">
 <code class="flex-1 min-w-0 bg-admin-theme-surface border border-blue-200 dark:border-blue-700 rounded-l px-3 py-2 text-sm font-mono text-admin-theme-text break-all whitespace-normal">
 {{ currentOrigin }}/api/webhooks/sepay
 </code>
 <button 
 type="button"
 @click="copyToClipboard(currentOrigin +'/api/webhooks/sepay')"
 class="shrink-0 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 px-3 py-2 rounded-r border-t border-r border-b border-blue-200 dark:border-blue-700 hover:bg-blue-200 dark:hover:bg-blue-700 font-medium text-sm"
 >
 {{ t('Copy') }}
 </button>
 </div>
 </div>
 <div>
 <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300 uppercase mb-1">{{ t('Events to Subscribe') }}</label>
 <div class="flex items-center gap-2">
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
 Incoming Transfer
 </span>
 </div>
 </div>
 </div>
 </div>

 <!-- Bank Accounts -->
 <div class="col-span-2 space-y-4">
 <div class="flex items-center justify-between">
 <h5 class="font-medium text-admin-theme-text">{{ t('Bank Accounts') }}</h5>
 <button 
 type="button"
 @click="addSepayBank"
 class="px-3 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content text-sm rounded hover:bg-admin-theme-primary-hover"
 >
 + {{ t('Add Bank') }}
 </button>
 </div>
 
 <div v-if="!editingConfig.config.banks || editingConfig.config.banks.length === 0" 
 class="text-sm text-admin-theme-text-muted italic">
 {{ t('No bank accounts configured. Add a bank account to enable SePay payments.') }}
 </div>
 
 <div v-for="(bank, idx) in editingConfig.config.banks" :key="idx" 
 class="border border-admin-theme-border rounded-lg p-4 space-y-3">
 <div class="grid grid-cols-1 gap-3">
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Bank Code') }} *</label>
 <input 
 type="text" 
 v-model="bank.bank_code"
 placeholder="ACB, VCB, BIDV, MB..."
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Bank Name') }}</label>
 <input 
 type="text" 
 v-model="bank.bank_name"
 placeholder="Asia Commercial Bank"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Number') }} *</label>
 <input 
 type="text" 
 v-model="bank.account_number"
 placeholder="7794181"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm font-mono"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Holder Name') }} *</label>
 <input 
 type="text" 
 v-model="bank.account_holder"
 placeholder="NGUYEN VAN A"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 </div>
 
 <!-- QR Code Preview Section -->
 <div class="pt-3 border-t border-admin-theme-border">
 <div class="flex flex-col gap-4">
 <div class="flex-1">
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Preview Amount (VND)') }}</label>
 <div class="flex flex-col gap-2">
 <input 
 type="number" 
 v-model.number="bank.preview_amount"
 placeholder="100000"
 class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 <button 
 type="button"
 @click="generateQrPreview(Number(idx))"
 :disabled="!bank.bank_code || !bank.account_number"
 class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
 >
 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
 </svg>
 {{ t('Generate QR') }}
 </button>
 </div>
 </div>
 
 <!-- QR Code Display -->
 <div v-if="bank.qr_preview_url" class="w-full flex justify-center">
 <div class="inline-flex flex-col items-center bg-white p-3 rounded-lg border border-gray-200 shadow-sm max-w-full">
 <img 
 :src="bank.qr_preview_url" 
 :alt="'QR Code for' + bank.bank_code"
 class="w-36 h-36 object-contain"
 @error="bank.qr_preview_url =''"
 />
 <p class="text-xs text-center text-gray-500 mt-2">
 {{ formatVND(bank.preview_amount || 0) }}
 </p>
 </div>
 </div>
 </div>
 </div>
 
 <div class="flex items-center justify-between pt-2 border-t border-admin-theme-border">
 <label class="flex items-center gap-2 cursor-pointer">
 <input 
 type="radio" 
 :name="'sepay_primary_' + gridModalMethod.code"
 :checked="bank.is_primary"
 @change="setSepayPrimaryBank(Number(idx))"
 class="text-admin-theme-primary"
 />
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Primary Bank') }}</span>
 </label>
 <button 
 type="button"
 @click="removeSepayBank(Number(idx))"
 class="text-red-500 hover:text-red-700 text-sm"
 >
 {{ t('Remove') }}
 </button>
 </div>
 </div>
 </div>
 
 <!-- API Key -->
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('SEPAY API Key') }}</label>
 <input 
 type="text" 
 v-model="editingConfig.config.api_key"
 placeholder="QFVCWSQDWV6Q1ALMWKBTIBYR3RHTFS2XE85PBY3OAOHVIIL..."
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text font-mono text-sm"
 />
 <p class="mt-1 text-xs text-admin-theme-text-muted">
 {{ t('API Key for webhook authentication from SEPAY. If left empty, webhook will not check authentication.') }}
 <br/>
 {{ t('Note: Header will be:') }} <code class="text-admin-theme-primary dark:text-admin-theme-primary">Authorization: Apikey YOUR_API_KEY</code>
 </p>
 </div>
 </template>

 <!-- COD specific -->
 <template v-else-if="gridModalMethod.code ==='cod'">
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Instructions') }}</label>
 <textarea 
 v-model="editingConfig.config.instructions"
 rows="3"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 :placeholder="t('Instructions for customers...')"
 ></textarea>
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Additional Fee') }}</label>
 <input 
 type="number" 
 v-model.number="editingConfig.config.additional_fee"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Fee Type') }}</label>
 <select 
 v-model="editingConfig.config.fee_type"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 >
 <option value="fixed">{{ t('Fixed Amount') }}</option>
 <option value="percentage">{{ t('Percentage') }}</option>
 </select>
 </div>
 </template>

 <!-- Bank Transfer specific -->
 <template v-else-if="gridModalMethod.code ==='bank_transfer'">
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Instructions') }}</label>
 <textarea 
 v-model="editingConfig.config.instructions"
 rows="3"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 :placeholder="t('Instructions for customers...')"
 ></textarea>
 </div>
 
 <!-- Bank Accounts -->
 <div class="col-span-2 space-y-4">
 <div class="flex items-center justify-between">
 <h5 class="font-medium text-admin-theme-text">{{ t('Bank Accounts') }}</h5>
 <button 
 type="button"
 @click="addBankTransferAccount"
 class="px-3 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content text-sm rounded hover:bg-admin-theme-primary-hover"
 >
 + {{ t('Add Bank') }}
 </button>
 </div>
 
 <div v-if="!editingConfig.config.banks || editingConfig.config.banks.length === 0" 
 class="text-sm text-admin-theme-text-muted italic">
 {{ t('No bank accounts configured.') }}
 </div>
 
 <div v-for="(bank, idx) in editingConfig.config.banks" :key="idx" 
 class="bank-item border border-admin-theme-border rounded-lg p-4 space-y-3 transition-all duration-200"
 :class="{ 
'opacity-40 border-2 border-dashed border-indigo-400 dark:border-admin-theme-primary scale-[0.98]': draggingBankIndex === idx, 
'bg-admin-theme-primary/10/50 border-admin-theme-primary ring-1 ring-indigo-500': dragOverBankIndex === idx 
 }"
 @dragover="handleBankDragOver(Number(idx), $event)"
 @drop="handleBankDrop(Number(idx), $event)"
 >
 <div class="flex items-start gap-4">
 <div class="flex items-start gap-3 flex-shrink-0">
 <div 
 class="cursor-move text-gray-400 hover:text-gray-600 mt-1"
 draggable="true"
 @dragstart="handleBankDragStart(Number(idx), $event)"
 @dragend="handleBankDragEnd"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
 </svg>
 </div>

 <div class="w-28 h-28 border rounded flex items-center justify-center bg-admin-theme-base cursor-pointer overflow-hidden flex-shrink-0"
 @click="selectBankLogo(idx)"
 :title="t('Click to change logo')">
 <img v-if="bank.logo" :src="bank.logo" class="w-full h-full object-contain" />
 <span v-else class="text-xs text-center text-gray-400">Logo</span>
 </div>
 </div>

 <div class="flex-1 min-w-0 space-y-3">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Bank Name') }}</label>
 <input 
 type="text" 
 v-model="bank.bank_name"
 placeholder="Asia Commercial Bank"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Number') }}</label>
 <input 
 type="text" 
 v-model="bank.account_number"
 placeholder="7794181"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm font-mono"
 />
 </div>
 </div>
 <div>
 <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Account Holder Name') }}</label>
 <input 
 type="text" 
 v-model="bank.account_holder"
 placeholder="NGUYEN VAN A"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 />
 </div>
 </div>
 </div>

 <div class="flex items-center justify-end pt-2">
 <button 
 type="button"
 @click="removeBankTransferAccount(Number(idx))"
 class="text-red-500 hover:text-red-700 text-sm"
 >
 {{ t('Remove') }}
 </button>
 </div>
 </div>
 </div>
 </template>

 <!-- Generic settings for other gateways -->
 <template v-else>
 <div class="text-sm text-admin-theme-text-muted">
 {{ t('Configure this gateway in the module settings.') }}
 </div>
 </template>

 <!-- Processing Fee (common) -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Processing Fee (%)') }}</label>
 <FormInput
 :name="`processing_fee_modal_${gridModalMethod.code}`"
 :model-value="editingConfig.config.processing_fee ??''"
 type="text"
 inputmode="decimal"
 :numeric-only="true"
 :allow-decimal="true"
 :rules="[{ type:'numeric', message: t('Processing fee must be a valid number') }]"
 validate-on="blur"
 :show-error="true"
 @update:model-value="handleProcessingFeeInput"
 />
 </div>
 </div>
 </div>

 <div class="mt-6 flex justify-end gap-3">
 <button @click="closeGridModal" class="px-4 py-2 text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-base">
 {{ t('Cancel') }}
 </button>
 <button @click="saveConfig(gridModalMethod)" :disabled="saving" class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50">
 {{ saving ? t('Saving...') : t('Save Changes') }}
 </button>
 </div>
 </div>
 </div>
 </div>

 <!-- Media Picker -->
 <MediaPicker
 ref="mediaPickerRef"
 :multiple="false"
 :accepted-types="['image']"
 @select="handleMediaSelect"
 />
 </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, watch } from'vue';
import axios from'axios';
import { useTranslation } from'../../composables/useTranslation';
import { useDialog } from'../../composables/useDialog';
import { useSortable } from'../../composables/useSortable';
import { Storage } from'../../utils';
import MediaPicker from'../../components/MediaPicker';
import FormInput from'../../components/forms/FormInput.vue';
import HelpGuide from'../../components/HelpGuide.vue';

const { t } = useTranslation();
const dialog = useDialog();

interface PaymentMethod {
 code: string;
 name: string;
 description: string | null;
 is_active: boolean;
 is_default: boolean;
 logo: string | null;
 sort_order: number;
 config: Record<string, any>;
}

const loading = ref(true);
const saving = ref(false);
const methods = ref<PaymentMethod[]>([]);
const expandedMethod = ref<string | null>(null);
const activePaypalTab = ref<'sandbox' | 'live'>('sandbox');
const viewMode = ref<'grid' |'list'>(Storage.get<'grid' |'list'>('payment_methods_view_mode') ||'list');
const filters = reactive({
 showActive: Storage.get<boolean>('payment_methods_filter_active', true),
 showInactive: Storage.get<boolean>('payment_methods_filter_inactive', true),
 search:'',
});
const gridModalOpen = ref(false);
const gridModalMethodCode = ref<string | null>(null);
const editingConfig = reactive<{
 name: string;
 description: string;
 logo: string;
 config: Record<string, any>;
}>({
 name:'',
 description:'',
 logo:'',
 config: {},
});

// Media picker
const mediaPickerRef = ref<any>(null);

// Bank logo selection state
const currentBankIndex = ref<number | null>(null);

// Drag and drop for methods
const {
 draggingIndex,
 dragOverIndex,
 handleDragStart,
 handleDragOver,
 handleDragEnd,
 handleDrop
} = useSortable(methods, {
 onSort: async (newItems) => {
 try {
 const order = newItems.map(m => m.code);
 await axios.post('/api/v1/payment-gateways/reorder', { order });
 dialog.success(t('Order saved'));
 } catch (error) {
 console.error('Error saving order:', error);
 dialog.error(t('Failed to save order'));
 loadMethods();
 }
 }
});

// Drag and drop for banks
const banksRef = computed({
 get: () => editingConfig.config.banks || [],
 set: (val) => { editingConfig.config.banks = val; }
});

const {
 draggingIndex: draggingBankIndex,
 dragOverIndex: dragOverBankIndex,
 handleDragStart: handleBankDragStart,
 handleDragOver: handleBankDragOver,
 handleDragEnd: handleBankDragEnd,
 handleDrop: handleBankDrop
} = useSortable(banksRef);

const testingPaypal = ref(false);
const paypalTestResult = ref<{ success: boolean; message: string } | null>(null);

const testPaypalConnection = async () => {
  testingPaypal.value = true;
  paypalTestResult.value = null;

  const isLive = activePaypalTab.value === 'live';
  const clientId = isLive ? editingConfig.config.live_client_id : editingConfig.config.sandbox_client_id;
  const clientSecret = isLive ? editingConfig.config.live_client_secret : editingConfig.config.sandbox_client_secret;

  try {
    const response = await axios.post('/api/v1/payment-gateways/paypal/test-connection', {
      client_id: clientId,
      client_secret: clientSecret,
      mode: activePaypalTab.value,
    });
    if (response.data && response.data.success) {
      paypalTestResult.value = {
        success: true,
        message: response.data.message || t('Connection successful! Credentials are valid.'),
      };
    } else {
      paypalTestResult.value = {
        success: false,
        message: response.data.message || t('Connection failed.'),
      };
    }
  } catch (error: any) {
    console.error('PayPal connection test error:', error);
    const errMsg = error.response?.data?.message || error.message || t('Connection failed.');
    paypalTestResult.value = {
      success: false,
      message: errMsg,
    };
  } finally {
    testingPaypal.value = false;
  }
};

const stats = computed(() => ({
 active: methods.value.filter((m) => m.is_active).length,
 inactive: methods.value.filter((m) => !m.is_active).length,
}));

const normalizedSearch = computed(() => filters.search.trim().toLowerCase());

const filteredMethods = computed(() => {
 return methods.value.filter((method) => {
 const byStatus = (filters.showActive && method.is_active) || (filters.showInactive && !method.is_active);
 if (!byStatus) return false;

 if (!normalizedSearch.value) return true;

 const keyword = normalizedSearch.value;
 const haystack = [
 method.name,
 method.code,
 method.description ||'',
 ].join('').toLowerCase();

 return haystack.includes(keyword);
 });
});

const canReorder = computed(() =>
 filters.showActive
 && filters.showInactive
 && !normalizedSearch.value
);

const listMethods = computed(() => (canReorder.value ? methods.value : filteredMethods.value));

const gridModalMethod = computed(() => {
 if (!gridModalMethodCode.value) return null;
 return methods.value.find((method) => method.code === gridModalMethodCode.value) ?? null;
});

watch(viewMode, (newMode) => {
 Storage.set('payment_methods_view_mode', newMode);
});

watch(() => filters.showActive, (val) => {
 Storage.set('payment_methods_filter_active', val);
});

watch(() => filters.showInactive, (val) => {
 Storage.set('payment_methods_filter_inactive', val);
});


// Helper to get current origin for Webhook URL
const currentOrigin = window.location.origin;

const copyToClipboard = async (text: string) => {
 try {
 await navigator.clipboard.writeText(text);
 dialog.success(t('Copied to clipboard!'));
 } catch (err) {
 console.error('Failed to copy:', err);
 dialog.error(t('Failed to copy to clipboard'));
 }
};

const loadMethods = async () => {
 loading.value = true;
 try {
 const response = await axios.get('/api/v1/payment-gateways');
 if (response.data && Array.isArray(response.data.data)) {
 methods.value = response.data.data.sort((a: PaymentMethod, b: PaymentMethod) => a.sort_order - b.sort_order);
 } else {
 console.error('Unexpected response format for payment gateways:', response.data);
 methods.value = [];
 }
 } catch (error) {
 console.error('Error loading payment methods:', error);
 } finally {
 loading.value = false;
 }
};

const prepareEditingConfig = (method: PaymentMethod) => {
 editingConfig.name = method.name;
 editingConfig.description = method.description ||'';
 editingConfig.logo = method.logo ||'';

 let config = method.config;
 if (typeof config ==='string') {
 try {
 config = JSON.parse(config);
 } catch (e) {
 config = {};
 }
 }

  if (method.code ==='paypal') {
  const currentMode = config.mode || 'sandbox';
  activePaypalTab.value = currentMode as 'sandbox' | 'live';
  editingConfig.config = {
  sandbox_client_id: config.sandbox_client_id || config.client_id || '',
  sandbox_client_secret: config.sandbox_client_secret || config.client_secret || '',
  sandbox_webhook_id: config.sandbox_webhook_id || config.webhook_id || '',
  live_client_id: config.live_client_id || '',
  live_client_secret: config.live_client_secret || '',
  live_webhook_id: config.live_webhook_id || '',
  mode: currentMode,
  processing_fee: 0,
  ...config,
  };
 } else if (method.code ==='sepay') {
 editingConfig.config = {
 banks: [],
 api_key:'',
 processing_fee: 0,
 ...config,
 };
 } else if (method.code ==='cod') {
 editingConfig.config = {
 instructions:'',
 additional_fee: 0,
 fee_type:'fixed',
 ...config,
 };
 } else if (method.code ==='bank_transfer') {
 editingConfig.config = {
 instructions:'',
 banks: [],
 ...config,
 };
 } else {
 editingConfig.config = {
 processing_fee: 0,
 ...config,
 };
 }

 if (editingConfig.config.processing_fee !== undefined && editingConfig.config.processing_fee !== null) {
 editingConfig.config.processing_fee = normalizeDecimalInput(editingConfig.config.processing_fee);
 }
};

const normalizeDecimalInput = (value: string | number): string => {
 let normalized = String(value ??'').trim().replace(',','.');
 normalized = normalized.replace(/[^0-9.]/g,'');
 const firstDot = normalized.indexOf('.');
 if (firstDot !== -1) {
 normalized = normalized.slice(0, firstDot + 1) + normalized.slice(firstDot + 1).replace(/\./g,'');
 }
 return normalized;
};

const handleProcessingFeeInput = (value: string | number) => {
 editingConfig.config.processing_fee = normalizeDecimalInput(value);
};

const toggleExpand = (code: string) => {
 paypalTestResult.value = null;
 testingPaypal.value = false;
 if (expandedMethod.value === code) {
 expandedMethod.value = null;
 return;
 }

 expandedMethod.value = code;
 const method = methods.value.find((m) => m.code === code);
 if (method) {
 prepareEditingConfig(method);
 }
};

const openGridModal = (method: PaymentMethod) => {
 paypalTestResult.value = null;
 testingPaypal.value = false;
 prepareEditingConfig(method);
 gridModalMethodCode.value = method.code;
 gridModalOpen.value = true;
};

const closeGridModal = () => {
 paypalTestResult.value = null;
 testingPaypal.value = false;
 gridModalOpen.value = false;
 gridModalMethodCode.value = null;
 currentBankIndex.value = null;
};

// Bank Transfer specific methods
const addBankTransferAccount = () => {
 if (!editingConfig.config.banks) {
 editingConfig.config.banks = [];
 }
 editingConfig.config.banks.push({
 bank_name:'',
 account_number:'',
 account_holder:'',
 logo:'',
 });
};

const removeBankTransferAccount = (index: number) => {
 if (editingConfig.config.banks) {
 editingConfig.config.banks.splice(index, 1);
 }
};

// Actions
const toggleActive = async (method: PaymentMethod) => {
 try {
 await axios.put(`/api/v1/payment-gateways/${method.code}`, {
 is_active: !method.is_active
 });
 method.is_active = !method.is_active;
 } catch (error) {
 console.error('Error toggling gateway:', error);
 }
};

const onMethodDragStart = (index: number, event: DragEvent) => {
 if (!canReorder.value) return;
 handleDragStart(index, event);
};

const onMethodDragOver = (index: number, event: DragEvent) => {
 if (!canReorder.value) return;
 handleDragOver(index, event);
};

const onMethodDrop = (index: number, event: DragEvent) => {
 if (!canReorder.value) return;
 handleDrop(index, event);
};

const onMethodDragEnd = () => {
 if (!canReorder.value) return;
 handleDragEnd();
};

const setDefault = async (code: string) => {
 try {
 await axios.post(`/api/v1/payment-gateways/${code}/set-default`);
 methods.value.forEach(m => {
 m.is_default = m.code === code;
 });
 } catch (error) {
 console.error('Error setting default:', error);
 }
};

const openMediaPicker = () => {
 currentBankIndex.value = null; // Reset to main logo mode
 if (mediaPickerRef.value) {
 mediaPickerRef.value.open();
 }
};

const selectBankLogo = (index: number | string) => {
 currentBankIndex.value = Number(index);
 if (mediaPickerRef.value) {
 mediaPickerRef.value.open();
 }
};

const handleMediaSelect = (media: any) => {
 if (media) {
 const selectedMedia = Array.isArray(media) ? media[0] : media;
 if (selectedMedia && selectedMedia.url) {
 if (currentBankIndex.value !== null && editingConfig.config.banks) {
 // Update bank logo
 if (editingConfig.config.banks[currentBankIndex.value]) {
 editingConfig.config.banks[currentBankIndex.value].logo = selectedMedia.url;
 }
 } else {
 // Update main method logo
 editingConfig.logo = selectedMedia.url;
 }
 }
 }
 // Reset index after selection
 currentBankIndex.value = null;
};

// SePay bank management methods
const addSepayBank = () => {
 if (!editingConfig.config.banks) {
 editingConfig.config.banks = [];
 }
 const isFirst = editingConfig.config.banks.length === 0;
 editingConfig.config.banks.push({
 bank_code:'',
 bank_name:'',
 account_number:'',
 account_holder:'',
 is_primary: isFirst, // First bank is primary by default
 });
};

const removeSepayBank = (index: number) => {
 if (editingConfig.config.banks) {
 const wasPrimary = editingConfig.config.banks[index]?.is_primary;
 editingConfig.config.banks.splice(index, 1);
 // If removed bank was primary, set first one as primary
 if (wasPrimary && editingConfig.config.banks.length > 0) {
 editingConfig.config.banks[0].is_primary = true;
 }
 }
};

const setSepayPrimaryBank = (index: number) => {
 if (editingConfig.config.banks) {
 editingConfig.config.banks.forEach((bank: any, i: number) => {
 bank.is_primary = i === index;
 });
 }
};

// Generate QR code preview for a bank account
const generateQrPreview = (index: number) => {
 if (!editingConfig.config.banks || !editingConfig.config.banks[index]) {
 return;
 }
 
 const bank = editingConfig.config.banks[index];
 const bankCode = bank.bank_code?.toUpperCase() ||'';
 const accountNumber = bank.account_number ||'';
 const amount = bank.preview_amount || 100000;
 const description ='DEMO' + Date.now().toString().slice(-6);
 
 if (!bankCode || !accountNumber) {
 return;
 }
 
 // Generate QR URL using SePay API format
 const params = new URLSearchParams({
 bank: bankCode,
 acc: accountNumber,
 amount: amount.toString(),
 des: description,
 });
 
 bank.qr_preview_url = `https://qr.sepay.vn/img?${params.toString()}`;
};

// Format VND currency
const formatVND = (amount: number): string => {
 return new Intl.NumberFormat('vi-VN', {
 style:'currency',
 currency:'VND',
 }).format(amount);
};

const saveConfig = async (method: PaymentMethod) => {
 if (editingConfig.config.processing_fee !== undefined && editingConfig.config.processing_fee !== null && editingConfig.config.processing_fee !=='') {
 const parsedProcessingFee = Number(editingConfig.config.processing_fee);
 if (!Number.isFinite(parsedProcessingFee)) {
 dialog.error(t('Processing fee must be a valid number'));
 return;
 }
 editingConfig.config.processing_fee = parsedProcessingFee;
 }

 saving.value = true;
 try {
 await axios.put(`/api/v1/payment-gateways/${method.code}`, {
 name: editingConfig.name,
 description: editingConfig.description,
 logo: editingConfig.logo,
 config: editingConfig.config,
 });
 
 // Update local data
 method.name = editingConfig.name;
 method.description = editingConfig.description;
 method.logo = editingConfig.logo;
 method.config = { ...editingConfig.config };
 
 // Show success toast - don't collapse the panel
 dialog.success(t('Settings saved successfully!'));

 if (gridModalOpen.value) {
 closeGridModal();
 }
 } catch (error) {
 console.error('Error saving config:', error);
 dialog.error(t('Failed to save settings'));
 } finally {
 saving.value = false;
 }
};

onMounted(() => loadMethods());
</script>
