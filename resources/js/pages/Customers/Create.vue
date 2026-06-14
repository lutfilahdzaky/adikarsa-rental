<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { router } from '@inertiajs/vue3';

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    password: '',
    password_confirmation: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const handleSubmit = async () => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        router.post('/customers', form.value, {
            onError: (pageErrors) => {
                errors.value = pageErrors;
            },
        });
    } finally {
        isSubmitting.value = false;
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Customers', href: '/customers' },
            { title: 'Add Customer' },
        ],
    },
});
</script>

<template>
    <Head title="Add Customer" />

    <div class="flex flex-col gap-6 px-6 py-6">
        <Heading variant="small" title="Add Customer" description="Create a new customer." />

        <div class="rounded-xl border border-sidebar-border/70 bg-background p-6 shadow-sm w-full max-w-3xl">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium">Name *</label>
                    <input v-model="form.name" type="text" required class="mt-2 block w-full rounded-lg border border-input px-3 py-2" />
                    <p v-if="errors.name" class="mt-1 text-sm text-destructive">{{ errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Email *</label>
                    <input v-model="form.email" type="email" required class="mt-2 block w-full rounded-lg border border-input px-3 py-2" />
                    <p v-if="errors.email" class="mt-1 text-sm text-destructive">{{ errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Phone *</label>
                    <input v-model="form.phone" type="text" required class="mt-2 block w-full rounded-lg border border-input px-3 py-2" />
                    <p v-if="errors.phone" class="mt-1 text-sm text-destructive">{{ errors.phone }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Address *</label>
                    <textarea v-model="form.address" rows="3" required class="mt-2 block w-full rounded-lg border border-input px-3 py-2"></textarea>
                    <p v-if="errors.address" class="mt-1 text-sm text-destructive">{{ errors.address }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Password *</label>
                    <input v-model="form.password" type="password" required class="mt-2 block w-full rounded-lg border border-input px-3 py-2" />
                    <p v-if="errors.password" class="mt-1 text-sm text-destructive">{{ errors.password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Confirm Password *</label>
                    <input v-model="form.password_confirmation" type="password" required class="mt-2 block w-full rounded-lg border border-input px-3 py-2" />
                    <p v-if="errors.password_confirmation" class="mt-1 text-sm text-destructive">{{ errors.password_confirmation }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Adding...' : 'Add Customer' }}
                    </button>
                    <a href="/customers" class="inline-flex items-center justify-center rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground hover:bg-muted">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>
