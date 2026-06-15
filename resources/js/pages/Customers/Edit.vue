<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    address?: string | null;
}

const props = defineProps<{ customer: Customer }>();

const form = ref({
    name: props.customer.name,
    email: props.customer.email,
    phone: props.customer.phone ?? '',
    address: props.customer.address ?? '',
    password: '',
    password_confirmation: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const handleSubmit = async () => {
    isSubmitting.value = true;
    errors.value = {};

        try {
            const formData = new FormData()
            formData.append('name', form.value.name)
            formData.append('email', form.value.email)
            formData.append('phone', form.value.phone)
            formData.append('address', form.value.address)
            if (form.value.password) {
                formData.append('password', form.value.password)
                formData.append('password_confirmation', form.value.password_confirmation)
            }

            formData.append('_method', 'PUT')

            await router.post(`/customers/${props.customer.id}`, formData, {
                onError: (pageErrors) => {
                    errors.value = pageErrors;
                },
            });
        } finally {
            isSubmitting.value = false;
        }
};

const handleDelete = async () => {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(`/customers/${props.customer.id}`);
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Customers', href: '/customers' },
            { title: 'Edit Customer' },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit: ${props.customer.name}`" />

    <div class="flex flex-col gap-6 px-6 py-6">
        <Heading :title="`Edit: ${props.customer.name}`" description="Update customer details." variant="small" />

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

                    <label class="block text-sm font-medium">New Password (leave blank to keep current)</label>
                    <input v-model="form.password" type="password" class="mt-2 block w-full rounded-lg border border-input px-3 py-2" />
                    <p v-if="errors.password" class="mt-1 text-sm text-destructive">{{ errors.password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Confirm New Password</label>
                    <input v-model="form.password_confirmation" type="password" class="mt-2 block w-full rounded-lg border border-input px-3 py-2" />
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="isSubmitting" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">{{ isSubmitting ? 'Updating...' : 'Update Customer' }}</button>
                    <button type="button" @click="handleDelete" class="inline-flex items-center justify-center rounded-lg bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground hover:bg-destructive/90">Delete</button>
                    <a href="/customers" class="inline-flex items-center justify-center rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground hover:bg-muted">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>
