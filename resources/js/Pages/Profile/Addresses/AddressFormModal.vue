<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CountryRegionSelect from '@/admin/components/Form/CountryRegionSelect.vue';
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();

const props = defineProps<{
    show: boolean;
    address?: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    full_name: '',
    phone: '',
    address_line: '',
    city: '',
    postal_code: '',
    country: 'VN',
    is_default: false,
});

const countrySelection = ref<any>({ country: 'VN', state: null });

watch(countrySelection, (newVal) => {
    if (newVal && newVal.country) {
        form.country = newVal.country;
        if (newVal.state) {
            form.city = newVal.state;
        }
    } else {
        form.country = '';
    }
}, { deep: true });

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.address) {
            form.full_name = props.address.full_name || '';
            form.phone = props.address.phone || '';
            form.address_line = props.address.address_line || '';
            form.city = props.address.city || '';
            form.postal_code = props.address.postal_code || '';
            form.country = props.address.country || 'VN';
            form.is_default = props.address.is_default || false;
            countrySelection.value = { country: form.country, state: null };
        } else {
            form.reset();
            countrySelection.value = { country: 'VN', state: null };
        }
        form.clearErrors();
    }
});

const submit = () => {
    if (props.address) {
        form.put(route('account.addresses.update', props.address.id), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('account.addresses.store'), {
            onSuccess: () => emit('close'),
        });
    }
};

const close = () => {
    emit('close');
    form.reset();
};
</script>

<template>
    <Modal :show="show" @close="close" :overflowVisible="true">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ address ? t('Edit Address') : t('Add New Address') }}
            </h2>

            <div class="mt-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div>
                        <InputLabel for="full_name" :value="t('Full Name')" class="required" />
                        <TextInput id="full_name" v-model="form.full_name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.full_name" class="mt-2" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <InputLabel for="phone" :value="t('Phone')" class="required" />
                        <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.phone" class="mt-2" />
                    </div>
                </div>

                <!-- Address Line -->
                <div>
                    <InputLabel for="address_line" :value="t('Address Line')" class="required" />
                    <TextInput id="address_line" v-model="form.address_line" type="text" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.address_line" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- City -->
                    <div>
                        <InputLabel for="city" :value="t('City / Province')" class="required" />
                        <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.city" class="mt-2" />
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <InputLabel for="postal_code" :value="t('Postal Code')" />
                        <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.postal_code" class="mt-2" />
                    </div>
                </div>

                <!-- Country -->
                <div>
                    <InputLabel for="country" :value="t('Country')" class="required" />
                    <CountryRegionSelect 
                        v-model="countrySelection" 
                        :multiple="false" 
                        :error="!!form.errors.country" 
                    />
                    <InputError :message="form.errors.country" class="mt-2" />
                </div>

                <!-- Set as default -->
                <div class="block mt-4">
                    <label class="flex items-center">
                        <Checkbox name="is_default" v-model:checked="form.is_default" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ t('Set as default address') }}</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="close" class="mr-3">
                    {{ t('Cancel') }}
                </SecondaryButton>
                <PrimaryButton @click="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('Save') }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
