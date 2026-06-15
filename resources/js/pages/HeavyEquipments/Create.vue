<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { router } from '@inertiajs/vue3';

const form = ref({
    name: '',
    description: '',
    daily_rate: 0,
    photo: null as File | null,
});

const photoPreview = ref<string | null>(null)

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const handleSubmit = async () => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        const formData = new FormData()
        formData.append('name', form.value.name)
        formData.append('description', form.value.description)
        formData.append('daily_rate', String(form.value.daily_rate))

        if (form.value.photo) {
            formData.append('photo', form.value.photo)
        }

        await router.post('/heavy-equipments', formData, {
            onError: (pageErrors) => {
                errors.value = pageErrors;
            },
        })
    } finally {
        isSubmitting.value = false;
    }
};

const handlePhotoChange = (event: Event) => {
    const target = event.target as HTMLInputElement | null
    const file = target?.files?.[0] ?? null

    form.value.photo = file

    if (file) {
        photoPreview.value = URL.createObjectURL(file)
    } else {
        photoPreview.value = null
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Heavy Equipments',
                href: '/heavy-equipments',
            },
            {
                title: 'Add Equipment',
            },
        ],
    },
});
</script>

<template>
    <Head title="Add Heavy Equipment" />

    <div class="flex flex-col gap-6 px-6 py-6">
        <Heading
            variant="small"
            title="Add Heavy Equipment"
            description="Add a new equipment to your rental fleet."
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
                    <label for="daily_rate" class="block text-sm font-medium text-foreground">Daily Rate (Rp) *</label>
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
                    <label for="photo" class="block text-sm font-medium text-foreground">Photo</label>
                    <input
                        id="photo"
                        type="file"
                        accept="image/*"
                        @change="handlePhotoChange"
                        class="mt-2 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder-muted-foreground focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                    <p v-if="errors.photo" class="mt-1 text-sm text-destructive">{{ errors.photo }}</p>

                    <div v-if="photoPreview" class="mt-3">
                        <p class="text-xs text-muted-foreground mb-1">Preview:</p>
                        <img :src="photoPreview" alt="preview" class="h-28 w-40 rounded object-cover border" />
                    </div>
                </div>


                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Adding...' : 'Add Equipment' }}
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
