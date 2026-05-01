<template>
    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEdit ? ($t('Edit Banner') || 'Edit Banner') : ($t('Create Banner') || 'Create New Banner') }}
            </h1>
            <router-link
                :to="{ name: 'admin.banner-slider.banners' }"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium whitespace-nowrap"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>{{ $t('Back to List') || 'Back to List' }}</span>
            </router-link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- Form Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form @submit.prevent="saveBanner">
            <!-- Banner Type -->
            <div class="mb-6">
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Banner Type
                </label>
                <select
                    v-model="form.type"
                    id="type"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                >
                    <option value="image">Image Banner</option>
                    <option value="text">Text Banner</option>
                </select>
            </div>

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Title <span class="text-gray-400">(optional)</span>
                </label>
                <input
                    v-model="form.title"
                    type="text"
                    id="title"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Banner title"
                />
            </div>

            <!-- Image (only for image type) -->
            <div v-if="form.type === 'image'" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Banner Image <span class="text-red-500">*</span>
                </label>
                <div v-if="selectedImage" class="mb-4">
                    <img :src="selectedImage.url" :alt="selectedImage.name" class="w-full max-w-md h-48 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                    <button
                        type="button"
                        @click="removeImage"
                        class="mt-2 text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                    >
                        Remove Image
                    </button>
                </div>
                <button
                    type="button"
                    @click="openMediaPicker"
                    class="px-4 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-indigo-500 dark:hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                >
                    {{ selectedImage ? 'Change Image' : 'Select Image' }}
                </button>
                <p v-if="errors.image_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.image_id }}</p>
            </div>

            <!-- Link Settings -->
            <div class="mb-6" v-if="form.link">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Link Settings
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="link_target" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                            Target
                        </label>
                        <select
                            v-model="form.link_target"
                            id="link_target"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="_self">Same Window</option>
                            <option value="_blank">New Window</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="link_rel" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                            Rel <span class="text-gray-400">(optional)</span>
                        </label>
                        <select
                            v-model="form.link_rel"
                            id="link_rel"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="">None</option>
                            <option value="noopener">noopener</option>
                            <option value="noreferrer">noreferrer</option>
                            <option value="noopener noreferrer">noopener noreferrer</option>
                            <option value="nofollow">nofollow</option>
                            <option value="nofollow noopener">nofollow noopener</option>
                            <option value="nofollow noopener noreferrer">nofollow noopener noreferrer</option>
                            <option value="sponsored">sponsored</option>
                            <option value="sponsored nofollow">sponsored nofollow</option>
                            <option value="ugc">ugc</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Link URL -->
            <div class="mb-6">
                <label for="link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Link URL <span class="text-gray-400">(optional)</span>
                </label>
                <input
                    v-model="form.link"
                    @blur="handleLinkChange"
                    type="url"
                    id="link"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="https://example.com"
                />
                <p v-if="errors.link" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.link }}</p>
            </div>

            <!-- Text Banner Content -->
            <div v-if="form.type === 'text'" class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Content <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="form.content"
                    id="content"
                    rows="5"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Banner text content"
                ></textarea>
                <p v-if="errors.content" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.content }}</p>
            </div>

            <!-- CTA Button Section (for text banners) -->
            <div v-if="form.type === 'text'" class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">CTA Button Settings</h3>

                <!-- Button Text -->
                <div class="mb-4">
                    <label for="button_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Button Text <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.button_text"
                        type="text"
                        id="button_text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        placeholder="Click Here"
                    />
                </div>

                <!-- Button Link -->
                <div class="mb-4" v-if="form.button_text">
                    <label for="button_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Button Link <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.button_link"
                        @blur="handleButtonLinkChange"
                        type="url"
                        id="button_link"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        placeholder="https://example.com"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">If not set, banner link will be used</p>
                </div>

                <!-- Button Target & Rel -->
                <div class="mb-4" v-if="form.button_text">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Button Link Settings
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="button_target" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Target
                            </label>
                            <select
                                v-model="form.button_target"
                                id="button_target"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                <option value="_self">Same Window</option>
                                <option value="_blank">New Window</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="button_rel" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Rel <span class="text-gray-400">(optional)</span>
                            </label>
                            <select
                                v-model="form.button_rel"
                                id="button_rel"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                <option value="">None</option>
                                <option value="noopener">noopener</option>
                                <option value="noreferrer">noreferrer</option>
                                <option value="noopener noreferrer">noopener noreferrer</option>
                                <option value="nofollow">nofollow</option>
                                <option value="nofollow noopener">nofollow noopener</option>
                                <option value="nofollow noopener noreferrer">nofollow noopener noreferrer</option>
                                <option value="sponsored">sponsored</option>
                                <option value="sponsored nofollow">sponsored nofollow</option>
                                <option value="ugc">ugc</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Color Settings (for text banners) -->
            <div v-if="form.type === 'text'" class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Color Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Background Color -->
                    <div>
                        <label for="background_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Background Color
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.background_color"
                                type="color"
                                id="background_color"
                                class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                            />
                            <input
                                v-model="form.background_color"
                                type="text"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="#ffffff"
                                pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                            />
                        </div>
                    </div>

                    <!-- Text Color -->
                    <div>
                        <label for="text_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Text Color
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.text_color"
                                type="color"
                                id="text_color"
                                class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                            />
                            <input
                                v-model="form.text_color"
                                type="text"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="#000000"
                                pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                            />
                        </div>
                    </div>

                    <!-- Button Background Color -->
                    <div v-if="form.button_text">
                        <label for="button_bg_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Button Background Color
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.button_bg_color"
                                type="color"
                                id="button_bg_color"
                                class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                            />
                            <input
                                v-model="form.button_bg_color"
                                type="text"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="#2563eb"
                                pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                            />
                        </div>
                    </div>

                    <!-- Button Text Color -->
                    <div v-if="form.button_text">
                        <label for="button_text_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Button Text Color
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.button_text_color"
                                type="color"
                                id="button_text_color"
                                class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                            />
                            <input
                                v-model="form.button_text_color"
                                type="text"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="#ffffff"
                                pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                            />
                        </div>
                    </div>
                </div>

                <!-- Button Gradient Settings -->
                <div v-if="form.button_text" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Button Gradient Background (Optional)
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Gradient requires both colors. If only one gradient color is set, button background color will not display.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="button_gradient_color" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Button Gradient Color 2
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.button_gradient_color"
                                    type="color"
                                    id="button_gradient_color"
                                    class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                                />
                                <input
                                    v-model="form.button_gradient_color"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="#000000"
                                    pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                />
                            </div>
                        </div>
                        <div>
                            <label for="button_gradient_degree" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Button Gradient Degree
                            </label>
                            <input
                                v-model.number="form.button_gradient_degree"
                                type="number"
                                id="button_gradient_degree"
                                min="0"
                                max="360"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Button Hover Settings -->
                <div v-if="form.button_text" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Button Hover Styles (Optional)
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="button_hover_bg_color" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Hover Background Color
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.button_hover_bg_color"
                                    type="color"
                                    id="button_hover_bg_color"
                                    class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                                />
                                <input
                                    v-model="form.button_hover_bg_color"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="#1e40af"
                                    pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                />
                            </div>
                        </div>
                        <div>
                            <label for="button_hover_text_color" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Hover Text Color
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.button_hover_text_color"
                                    type="color"
                                    id="button_hover_text_color"
                                    class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                                />
                                <input
                                    v-model="form.button_hover_text_color"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="#ffffff"
                                    pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                />
                            </div>
                        </div>
                        <div>
                            <label for="button_hover_gradient_color" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Hover Gradient Color (Optional)
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.button_hover_gradient_color"
                                    type="color"
                                    id="button_hover_gradient_color"
                                    class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                                />
                                <input
                                    v-model="form.button_hover_gradient_color"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="#000000"
                                    pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Requires Hover Background Color to create gradient</p>
                        </div>
                    </div>
                </div>

                <!-- Background Color -->
                <div class="mt-4">
                    <label for="background_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Background Color
                    </label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.background_color"
                            type="color"
                            id="background_color"
                            class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                        />
                        <input
                            v-model="form.background_color"
                            type="text"
                            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="#ffffff"
                            pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                        />
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Used as solid background (only if gradient is not set)</p>
                </div>

                <!-- Gradient Settings -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Gradient Background (Optional)
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Gradient requires both colors. If only one gradient color is set, background color will not display.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="gradient_color" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Gradient Color 2
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.gradient_color"
                                    type="color"
                                    id="gradient_color"
                                    class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                                />
                                <input
                                    v-model="form.gradient_color"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="#2563eb"
                                    pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                />
                            </div>
                        </div>
                        <div>
                            <label for="gradient_degree" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                Gradient Degree (0-360°)
                            </label>
                            <div class="flex gap-2 items-center">
                                <input
                                    v-model.number="form.gradient_degree"
                                    type="range"
                                    id="gradient_degree"
                                    min="0"
                                    max="360"
                                    class="flex-1"
                                />
                                <span class="text-sm text-gray-600 dark:text-gray-400 w-16 text-right">{{ form.gradient_degree || 135 }}°</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        v-model="form.active"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
            </div>

            <!-- Start Date and End Date -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Start Date <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            v-model="form.start_date"
                            type="datetime-local"
                            id="start_date"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Banner will be active from this date</p>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            End Date <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            v-model="form.end_date"
                            type="datetime-local"
                            id="end_date"
                            :min="form.start_date"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Banner will be active until this date</p>
                        <p v-if="errors.end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.end_date }}</p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description <span class="text-gray-400">(optional)</span>
                </label>
                <textarea
                    v-model="form.description"
                    id="description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Banner description or alt text"
                ></textarea>
            </div>

            <!-- Countdown Settings -->
            <div v-if="form.type === 'text'" class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Countdown Settings</h3>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input
                            v-model="form.countdown_enabled"
                            type="checkbox"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Countdown</span>
                    </label>
                </div>
                <div v-if="form.countdown_enabled" class="mb-4">
                    <label for="countdown_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Countdown Date & Time
                    </label>
                    <input
                        v-model="form.countdown_date"
                        type="datetime-local"
                        id="countdown_date"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The countdown will display time remaining until this date</p>
                </div>
            </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="router.back()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="loading || (form.type === 'image' && !selectedImage) || (form.type === 'text' && !form.content)"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ loading ? 'Saving...' : (isEdit ? 'Update Banner' : 'Create Banner') }}
                </button>
            </div>
                </form>

                <!-- Media Picker -->
                <MediaPicker
                    ref="mediaPickerRef"
                    :multiple="false"
                    :accepted-types="['image']"
                    @select="handleMediaSelect"
                />
            </div>

            <!-- Preview Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 static lg:sticky lg:top-[80px] lg:self-start lg:max-h-[calc(100vh-100px)] overflow-y-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preview</h2>
                <div class="banner-preview-container">
                    <div v-if="form.type === 'image'" class="banner-preview-image">
                        <div v-if="selectedImage" class="relative">
                            <img :src="selectedImage.url" :alt="form.title || 'Banner'" class="w-full h-auto rounded-lg" />
                            <div v-if="form.title || form.description" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 rounded-b-lg">
                                <h3 v-if="form.title" class="text-white font-semibold mb-1">{{ form.title }}</h3>
                                <p v-if="form.description" class="text-white/90 text-sm">{{ form.description }}</p>
                            </div>
                        </div>
                        <div v-else class="flex items-center justify-center h-48 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <p class="text-gray-400 dark:text-gray-500">No image selected</p>
                        </div>
                    </div>
                    <div v-else class="banner-preview-text" :style="previewStyle">
                        <div class="banner-preview-content">
                            <h3 v-if="form.title" class="banner-preview-title">{{ form.title }}</h3>
                            <div class="banner-preview-body-wrapper">
                                <div v-if="form.content" class="banner-preview-body">{{ form.content }}</div>
                                <a
                                    v-if="form.button_text"
                                    href="#"
                                    class="banner-preview-button"
                                    :style="buttonStyle"
                                    :data-hover-bg="buttonHoverStyle.backgroundColor || ''"
                                    :data-hover-color="buttonHoverStyle.color || ''"
                                    :data-hover-background="buttonHoverStyle.background || ''"
                                >
                                    {{ form.button_text }}
                                </a>
                            </div>
                            <div v-if="form.countdown_enabled && form.countdown_date" class="banner-preview-countdown" ref="countdownPreviewRef">
                                <div class="countdown-item">
                                    <span class="countdown-value" data-days>{{ countdownValues.days }}</span>
                                    <span class="countdown-label">DAYS</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-value" data-hours>{{ countdownValues.hours }}</span>
                                    <span class="countdown-label">HOURS</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-value" data-minutes>{{ countdownValues.minutes }}</span>
                                    <span class="countdown-label">MINUTES</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-value" data-seconds>{{ countdownValues.seconds }}</span>
                                    <span class="countdown-label">SECONDS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUpdated, watch, getCurrentInstance, onUnmounted, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import MediaPicker from '@admin/components/MediaPicker';
import { useDialog } from '@admin/composables/useDialog';
import { useTranslation } from '@admin/composables/useTranslation';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const route = useRoute();
const dialog = useDialog();

const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const loading = ref(false);
const errors = ref<Record<string, string>>({});

interface MediaItem {
    id: number;
    name: string;
    file_name: string;
    url: string;
    type: string;
    size: number;
    created_at: string;
    width?: number;
    height?: number;
}

const selectedImage = ref<MediaItem | null>(null);
const countdownPreviewRef = ref<HTMLElement | null>(null);

const isEdit = computed(() => !!route.params.id);

// Countdown values for preview
const countdownValues = ref({
    days: '00',
    hours: '00',
    minutes: '00',
    seconds: '00',
});

let countdownInterval: number | null = null;

// Countdown function for preview
function updateCountdownPreview() {
    if (!form.value.countdown_enabled || !form.value.countdown_date) {
        countdownValues.value = { days: '00', hours: '00', minutes: '00', seconds: '00' };
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        return;
    }

    const target = new Date(form.value.countdown_date).getTime();
    const now = new Date().getTime();
    const distance = target - now;

    if (distance < 0) {
        countdownValues.value = { days: '00', hours: '00', minutes: '00', seconds: '00' };
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    countdownValues.value = {
        days: String(days).padStart(2, '0'),
        hours: String(hours).padStart(2, '0'),
        minutes: String(minutes).padStart(2, '0'),
        seconds: String(seconds).padStart(2, '0'),
    };
}

// Watch countdown date to update preview - will be set up after form is initialized

// Preview computed styles
const previewStyle = computed(() => {
    if (form.value.type !== 'text') return {};

    const bgColor = form.value.background_color || null;
    const textColor = form.value.text_color || '#000000';
    const gradientColor1 = form.value.background_color || null;
    const gradientColor2 = form.value.gradient_color || null;
    const gradientDegree = form.value.gradient_degree || 135;

    let style = `color: ${textColor};`;
    // Chỉ hiển thị gradient nếu có cả 2 màu gradient
    if (gradientColor1 && gradientColor2) {
        // Có cả 2 màu gradient -> dùng gradient
        style += ` background: linear-gradient(${gradientDegree}deg, ${gradientColor1}, ${gradientColor2});`;
    } else if (bgColor && !gradientColor2) {
        // Chỉ có background color (không có gradient color 2) -> dùng background color
        style += ` background-color: ${bgColor};`;
    }
    // Nếu chỉ có gradient color 2 mà không có background color -> không hiển thị background

    return style;
});

const buttonStyle = computed(() => {
    if (!form.value.button_text) return {};

    const bgColor = form.value.button_bg_color || '#2563eb';
    const textColor = form.value.button_text_color || '#ffffff';
    const gradientColor1 = form.value.button_bg_color || null;
    const gradientColor2 = form.value.button_gradient_color || null;
    const gradientDegree = form.value.button_gradient_degree || 135;

    let style: any = {
        color: textColor,
    };

    // Apply gradient if both colors are set, otherwise use solid background
    if (gradientColor1 && gradientColor2) {
        style.background = `linear-gradient(${gradientDegree}deg, ${gradientColor1}, ${gradientColor2})`;
    } else if (bgColor && !gradientColor2) {
        style.backgroundColor = bgColor;
    }

    return style;
});

const buttonHoverStyle = computed(() => {
    if (!form.value.button_text) return {};

    const hoverBgColor = form.value.button_hover_bg_color || null;
    const hoverTextColor = form.value.button_hover_text_color || null;
    const hoverGradientColor1 = form.value.button_hover_bg_color || null;
    const hoverGradientColor2 = form.value.button_hover_gradient_color || null;
    const gradientDegree = form.value.button_gradient_degree || 135;

    if (!hoverBgColor && !hoverTextColor) return {};

    let style: any = {};

    if (hoverTextColor) {
        style.color = hoverTextColor;
    }

    // Apply gradient if both colors are set, otherwise use solid background
    if (hoverGradientColor1 && hoverGradientColor2) {
        style.background = `linear-gradient(${gradientDegree}deg, ${hoverGradientColor1}, ${hoverGradientColor2})`;
    } else if (hoverBgColor && !hoverGradientColor2) {
        style.backgroundColor = hoverBgColor;
    }

    return style;
});

const form = ref({
    title: '',
    type: 'image',
    image_id: null as number | null,
    link: '',
    link_target: '_self',
    link_rel: '',
    active: true,
    start_date: '',
    end_date: '',
    description: '',
    content: '',
    button_text: '',
    button_link: '',
    button_target: '_self',
    button_rel: '',
    background_color: '',
    button_bg_color: '',
    button_text_color: '',
    text_color: '',
    gradient_color: '',
    gradient_degree: 135,
    button_gradient_color: '',
    button_gradient_degree: 135,
    button_hover_bg_color: '',
    button_hover_text_color: '',
    button_hover_gradient_color: '',
    countdown_enabled: false,
    countdown_date: '',
});

// Get current domain for rel auto-detection
const getCurrentDomain = (): string => {
    return window.location.origin;
};

// Check if URL is external (not same domain)
const isExternalUrl = (url: string): boolean => {
    if (!url) return false;
    try {
        const urlObj = new URL(url);
        const currentDomain = new URL(getCurrentDomain());
        return urlObj.hostname !== currentDomain.hostname;
    } catch {
        return false;
    }
};

// Auto-set nofollow for external URLs
const handleLinkChange = () => {
    if (form.value.link && isExternalUrl(form.value.link) && !form.value.link_rel) {
        form.value.link_rel = 'nofollow';
    }
};

const handleButtonLinkChange = () => {
    if (form.value.button_link && isExternalUrl(form.value.button_link) && !form.value.button_rel) {
        form.value.button_rel = 'nofollow';
    }
};

const openMediaPicker = () => {
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (media: MediaItem | MediaItem[]) => {
    if (Array.isArray(media)) {
        selectedImage.value = media[0] || null;
    } else {
        selectedImage.value = media;
    }
    if (selectedImage.value) {
        form.value.image_id = selectedImage.value.id;
    }
};

const removeImage = () => {
    selectedImage.value = null;
    form.value.image_id = null;
};

const loadBanner = async () => {
    if (!route.params.id) return;

    try {
        const response = await axios.get(`/api/v1/banner-slider/banners/${route.params.id}`);
        const banner = response.data.data;

        form.value = {
            title: banner.title || '',
            type: banner.type || 'image',
            image_id: banner.image_id,
            link: banner.link || '',
            link_target: banner.link_target || '_self',
            link_rel: banner.link_rel || '',
            active: banner.active ?? true,
            start_date: banner.start_date ? new Date(banner.start_date).toISOString().slice(0, 16) : '',
            end_date: banner.end_date ? new Date(banner.end_date).toISOString().slice(0, 16) : '',
            description: banner.description || '',
            content: banner.content || '',
            button_text: banner.button_text || '',
            button_link: banner.button_link || '',
            button_target: banner.button_target || '_self',
            button_rel: banner.button_rel || '',
            background_color: banner.background_color || '',
            button_bg_color: banner.button_bg_color || '',
            button_text_color: banner.button_text_color || '',
            text_color: banner.text_color || '',
            gradient_color: banner.gradient_color || '',
            gradient_degree: banner.gradient_degree || 135,
            button_gradient_color: banner.button_gradient_color || '',
            button_gradient_degree: banner.button_gradient_degree || 135,
            button_hover_bg_color: banner.button_hover_bg_color || '',
            button_hover_text_color: banner.button_hover_text_color || '',
            button_hover_gradient_color: banner.button_hover_gradient_color || '',
            countdown_enabled: banner.countdown_enabled ?? false,
            countdown_date: banner.countdown_date ? new Date(banner.countdown_date).toISOString().slice(0, 16) : '',
        };

        if (banner.image) {
            selectedImage.value = {
                id: banner.image.id,
                name: banner.image.name,
                file_name: banner.image.file_name,
                url: banner.image.url,
                type: 'image',
                size: 0,
                created_at: '',
            };
        }
    } catch (error: any) {
        console.error('Error loading banner:', error);
        dialog.error('Failed to load banner');
        router.back();
    }
};

const saveBanner = async () => {
    // Validate based on type
    if (form.value.type === 'image' && !form.value.image_id) {
        errors.value.image_id = 'Banner image is required for image type banners';
        return;
    }
    if (form.value.type === 'text' && !form.value.content) {
        errors.value.content = 'Content is required for text type banners';
        return;
    }

    errors.value = {};
    loading.value = true;

    try {
        const payload: any = {
            title: form.value.title || null,
            type: form.value.type,
            image_id: form.value.type === 'image' ? form.value.image_id : null,
            link: form.value.link || null,
            link_target: form.value.link_target,
            link_rel: form.value.link_rel || null,
            // Order will be set automatically based on created_at timestamp
            active: form.value.active,
            description: form.value.description || null,
            content: form.value.type === 'text' ? form.value.content : null,
            button_text: form.value.button_text || null,
            button_link: form.value.button_link || null,
            button_target: form.value.button_target,
            button_rel: form.value.button_rel || null,
            background_color: form.value.background_color || null,
            button_bg_color: form.value.button_bg_color || null,
            button_text_color: form.value.button_text_color || null,
            text_color: form.value.text_color || null,
            gradient_color: form.value.gradient_color || null,
            gradient_degree: form.value.gradient_degree || 135,
            button_gradient_color: form.value.button_gradient_color || null,
            button_gradient_degree: form.value.button_gradient_degree || 135,
            button_hover_bg_color: form.value.button_hover_bg_color || null,
            button_hover_text_color: form.value.button_hover_text_color || null,
            button_hover_gradient_color: form.value.button_hover_gradient_color || null,
            countdown_enabled: form.value.countdown_enabled || false,
            countdown_date: form.value.countdown_date ? new Date(form.value.countdown_date).toISOString() : null,
        };

        if (form.value.start_date) {
            payload.start_date = new Date(form.value.start_date).toISOString();
        }

        if (form.value.end_date) {
            payload.end_date = new Date(form.value.end_date).toISOString();
        }

        if (isEdit.value) {
            await axios.put(`/api/v1/banner-slider/banners/${route.params.id}`, payload);
            dialog.success('Banner updated successfully');
            // Stay on edit page after update - reload banner to get latest data
            await loadBanner();
        } else {
            const response = await axios.post('/api/v1/banner-slider/banners', payload);
            dialog.success('Banner created successfully');
            // Redirect to edit page of the newly created banner
            if (response.data?.data?.id) {
                router.push({
                    name: 'admin.banner-slider.banners.edit',
                    params: { id: response.data.data.id }
                }).then(() => {
                    // Load the banner after navigation
                    loadBanner();
                });
            }
        }
    } catch (error: any) {
        console.error('Error saving banner:', error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            const message = error.response?.data?.message || 'Failed to save banner';
            dialog.error(message);
        }
    } finally {
        loading.value = false;
    }
};

// Apply hover styles to preview button
const applyPreviewButtonHover = () => {
    nextTick(() => {
        const buttons = document.querySelectorAll('.banner-preview-button[data-hover-bg], .banner-preview-button[data-hover-color], .banner-preview-button[data-hover-background]');
        buttons.forEach(button => {
            const hoverBg = button.getAttribute('data-hover-bg');
            const hoverColor = button.getAttribute('data-hover-color');
            const hoverBackground = button.getAttribute('data-hover-background');
            const originalStyle = button.getAttribute('style') || '';

            if (hoverBg || hoverColor || hoverBackground) {
                button.addEventListener('mouseenter', function(this: HTMLElement) {
                    if (hoverBackground) {
                        this.style.background = hoverBackground;
                    } else if (hoverBg) {
                        this.style.backgroundColor = hoverBg;
                    }
                    if (hoverColor) {
                        this.style.color = hoverColor;
                    }
                });

                button.addEventListener('mouseleave', function(this: HTMLElement) {
                    this.setAttribute('style', originalStyle);
                });
            }
        });
    });
};

onMounted(() => {
    if (isEdit.value) {
        loadBanner();
    }
    applyPreviewButtonHover();
});

onUpdated(() => {
    applyPreviewButtonHover();
});

// Watch for link changes to auto-set nofollow
watch(() => form.value.link, (newLink) => {
    if (newLink && isExternalUrl(newLink) && !form.value.link_rel) {
        form.value.link_rel = 'nofollow';
    }
});

watch(() => form.value.button_link, (newLink) => {
    if (newLink && isExternalUrl(newLink) && !form.value.button_rel) {
        form.value.button_rel = 'nofollow';
    }
});

// Watch countdown date to update preview
watch([() => form.value.countdown_enabled, () => form.value.countdown_date], () => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }

    updateCountdownPreview();

    if (form.value.countdown_enabled && form.value.countdown_date) {
        countdownInterval = window.setInterval(updateCountdownPreview, 1000);
    }
}, { immediate: true });

onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
});
</script>

<style scoped>
.banner-preview-container {
    border: 2px dashed #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
    background: #f9fafb;
}

.banner-preview-text {
    padding: 2rem;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.banner-preview-content {
    max-width: 100%;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.banner-preview-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    width: 100%;
}

.banner-preview-countdown {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    margin-top: 1rem;
}

.banner-preview-image img {
    max-height: 300px;
    object-fit: cover;
}

.banner-preview-title {
    text-align: center;
    width: 100%;
    margin: 0 0 1rem 0;
}

.banner-preview-body-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.banner-preview-button {
    display: inline-block;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    font-size: 1rem;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.2s ease-out;
    border: none;
    cursor: pointer;
}

.banner-preview-button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Apply hover styles dynamically */
.banner-preview-button[data-hover-bg],
.banner-preview-button[data-hover-color],
.banner-preview-button[data-hover-background] {
    transition: all 0.2s ease-out;
}

.banner-preview-body {
    display: inline;
}




</style>
