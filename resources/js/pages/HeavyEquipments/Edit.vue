<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { router } from '@inertiajs/vue3';

interface HeavyEquipment {
    id: number;
    name: string;
    description: string | null;
    daily_rate: number;
    photo: string | null;
    status: string;
}

interface Props {
    heavyEquipment: HeavyEquipment;
}

const props = defineProps<Props>();

const form = ref({
    name: props.heavyEquipment.name,
    description: props.heavyEquipment.description ?? '',
    daily_rate: props.heavyEquipment.daily_rate ?? 0,
    photo: props.heavyEquipment.photo ?? '',
    status: props.heavyEquipment.status,
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const handleSubmit = async () => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        router.put(`/heavy-equipments/${props.heavyEquipment.id}`, form.value, {
            onError: (pageErrors) => {
                errors.value = pageErrors;
            },
        });
    } finally {
        isSubmitting.value = false;
    }
};

const handleDelete = async () => {
    if (confirm('Are you sure you want to delete this equipment?')) {
        router.delete(`/heavy-equipments/${props.heavyEquipment.id}`);
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Heavy equipments',
                href: '/heavy-equipments',
            },
            {
                title: 'Edit Equipment',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit: ${heavyEquipment.name}`" />

    <div class="flex flex-col gap-6 px-6 py-6">
        <Heading
            variant="small"
            :title="`Edit: ${heavyEquipment.name}`"
            description="Update equipment details."
        />

        <div class="rounded-xl border border-sidebar-border/70 bg-background p-6 shadow-sm">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-foreground">Name *</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder-muted-foreground focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring"
                        placeholder="Equipment name"
                    />
                    <p v-if="errors.name" class="mt-1 text-sm text-destructive">{{ errors.name }}</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-foreground">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder-muted-foreground focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring"
                        placeholder="Equipment description"
                    ></textarea>
                    <p v-if="errors.description" class="mt-1 text-sm text-destructive">{{ errors.description }}</p>
                </div>

                <div>
                    <label for="daily_rate" class="block text-sm font-medium text-foreground">Daily Rate *</label>
                    <input
                        id="daily_rate"
                        v-model.number="form.daily_rate"
                        type="number"
                        step="1"
                        required
                        class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder-muted-foreground focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring"
                        placeholder="0.00"
                    />
                    <p v-if="errors.daily_rate" class="mt-1 text-sm text-destructive">{{ errors.daily_rate }}</p>
                </div>

                <div>
                    <label for="photo" class="block text-sm font-medium text-foreground">Photo URL</label>
                    <input
                        id="photo"
                        v-model="form.photo"
                        type="url"
                        class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder-muted-foreground focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring"
                        placeholder="https://example.com/photo.jpg"
                    />
                    <p v-if="errors.photo" class="mt-1 text-sm text-destructive">{{ errors.photo }}</p>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-foreground">Status *</label>
                    <select
                        id="status"
                        v-model="form.status"
                        required
                        class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring"
                    >
                        <option value="available">Available</option>
                        <option value="rented">Rented</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="offline">Offline</option>
                    </select>
                    <p v-if="errors.status" class="mt-1 text-sm text-destructive">{{ errors.status }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Updating...' : 'Update Equipment' }}
                    </button>
                    <button
                        type="button"
                        @click="handleDelete"
                        class="inline-flex items-center justify-center rounded-lg bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground hover:bg-destructive/90"
                    >
                        Delete
                    </button>
                    <a
                        href="/heavy-equipments"
                        class="inline-flex items-center justify-center rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</template>
