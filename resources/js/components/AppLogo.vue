<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const appName = page.props.name || import.meta.env.VITE_APP_NAME || 'Laravel';
</script>

<template>
    <div class="ml-1 grid flex-1 text-left">
        <span class="truncate text-2xl font-bold leading-tight">
            {{ appName }}
        </span>
    </div>
</template>
