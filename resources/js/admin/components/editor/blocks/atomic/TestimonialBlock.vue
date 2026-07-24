<template>
 <!-- Settings Mode -->
 <div v-if="mode ==='settings'" class="testimonial-block-settings space-y-4">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Quote</label>
 <textarea v-model="state.quote" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm h-24 focus:ring-2 focus:ring-admin-theme-primary" placeholder="Enter testimonial quote..."></textarea>
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Author Name</label>
 <input v-model="state.author_name" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="John Doe" />
 </div>
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Author Role</label>
 <input v-model="state.author_role" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="CEO, Company" />
 </div>
 </div>

 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Avatar URL</label>
 <input v-model="state.avatar_url" type="url" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="https://example.com/avatar.jpg" />
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Style</label>
 <select v-model="state.style" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11">
 <option value="card">Card</option>
 <option value="minimal">Minimal</option>
 <option value="bubble">Speech Bubble</option>
 </select>
 </div>
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Rating (0-5)</label>
 <select v-model.number="state.rating" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11">
 <option :value="0">No Rating</option>
 <option :value="1">★</option>
 <option :value="2">★★</option>
 <option :value="3">★★★</option>
 <option :value="4">★★★★</option>
 <option :value="5">★★★★★</option>
 </select>
 </div>
 </div>

 <AlignmentPicker v-model="state.alignment" label="Alignment" />
 </div>

 <!-- Preview Mode -->
 <div v-else class="landing-testimonial" :class="['landing-testimonial--' + state.style, 'text-' + state.alignment]">
 <div v-if="state.rating > 0" class="landing-testimonial__stars">
 <span v-for="i in state.rating" :key="i" class="landing-testimonial__star">★</span>
 </div>
 <blockquote class="landing-testimonial__quote">
 "{{ state.quote || 'This product changed the way we work. Highly recommended!' }}"
 </blockquote>
 <div class="landing-testimonial__author">
 <img v-if="state.avatar_url" :src="state.avatar_url" :alt="state.author_name" class="landing-testimonial__avatar" />
 <div v-else class="landing-testimonial__avatar landing-testimonial__avatar--placeholder">
 {{ (state.author_name || 'A').charAt(0).toUpperCase() }}
 </div>
 <div class="landing-testimonial__info">
 <div class="landing-testimonial__name">{{ state.author_name || 'John Doe' }}</div>
 <div class="landing-testimonial__role">{{ state.author_role || 'CEO, Company' }}</div>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';
import AlignmentPicker from '../../controls/AlignmentPicker.vue';

const props = defineProps<{
 modelValue: any;
 isEditor?: boolean;
 mode?: 'settings' | 'preview';
 data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
 quote: props.modelValue?.quote || props.data?.quote || '',
 author_name: props.modelValue?.author_name || props.data?.author_name || '',
 author_role: props.modelValue?.author_role || props.data?.author_role || '',
 avatar_url: props.modelValue?.avatar_url || props.data?.avatar_url || '',
 style: props.modelValue?.style || props.data?.style || 'card',
 rating: props.modelValue?.rating ?? props.data?.rating ?? 0,
 alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
});

const buildPayload = () => ({
 quote: state.quote,
 author_name: state.author_name,
 author_role: state.author_role,
 avatar_url: state.avatar_url,
 style: state.style,
 rating: state.rating,
 alignment: state.alignment,
});

watch(() => ({ ...state }), () => {
 if (props.mode === 'settings') {
 emit('update:modelValue', buildPayload());
 }
}, { deep: true });

watch(() => props.modelValue, (newVal) => {
 if (props.mode === 'preview' && newVal) {
 state.quote = newVal.quote || '';
 state.author_name = newVal.author_name || '';
 state.author_role = newVal.author_role || '';
 state.avatar_url = newVal.avatar_url || '';
 state.style = newVal.style || 'card';
 state.rating = newVal.rating ?? 0;
 state.alignment = newVal.alignment || 'left';
 }
}, { deep: true, immediate: true });
</script>
