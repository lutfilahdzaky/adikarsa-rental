<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

interface Stats {
    totalRentals: number;
    activeRentals: number;
    completedRentals: number;
    pendingRentals: number;
}

const page = usePage<{ stats: Stats }>();

const stats = page.props.stats;

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const statCards = [
    {
        title: 'Total Rentals',
        value: stats?.totalRentals ?? 0,
        color: 'bg-blue-50 dark:bg-blue-950',
        textColor: 'text-blue-600 dark:text-blue-400',
        borderColor: 'border-blue-200 dark:border-blue-700',
    },
    {
        title: 'Active Rentals',
        value: stats?.activeRentals ?? 0,
        color: 'bg-green-50 dark:bg-green-950',
        textColor: 'text-green-600 dark:text-green-400',
        borderColor: 'border-green-200 dark:border-green-700',
    },
    {
        title: 'Completed Rentals',
        value: stats?.completedRentals ?? 0,
        color: 'bg-purple-50 dark:bg-purple-950',
        textColor: 'text-purple-600 dark:text-purple-400',
        borderColor: 'border-purple-200 dark:border-purple-700',
    },
    {
        title: 'Pending Rentals',
        value: stats?.pendingRentals ?? 0,
        color: 'bg-amber-50 dark:bg-amber-950',
        textColor: 'text-amber-600 dark:text-amber-400',
        borderColor: 'border-amber-200 dark:border-amber-700',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div v-for="(card, index) in statCards" :key="index" :class="`relative overflow-hidden rounded-xl border ${card.borderColor} ${card.color} p-6`">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-muted-foreground">{{ card.title }}</p>
                    <p :class="`text-3xl font-bold ${card.textColor}`">{{ card.value }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
