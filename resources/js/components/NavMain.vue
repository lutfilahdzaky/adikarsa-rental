<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

interface NavGroup {
    label: string;
    items: NavItem[];
}

defineProps<{
    items: NavItem[] | NavGroup[];
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();

const isNavGroup = (item: any): item is NavGroup => {
    return 'label' in item && 'items' in item;
};
</script>

<template>
    <template v-for="item in items" :key="isNavGroup(item) ? item.label : item.title">
        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel>
                {{ isNavGroup(item) ? item.label : 'Management' }}
            </SidebarGroupLabel>
            <SidebarMenu>
                <SidebarMenuItem v-for="navItem in isNavGroup(item) ? item.items : [item]" :key="navItem.title">
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentOrParentUrl(navItem.href)"
                        :tooltip="navItem.title"
                    >
                        <Link :href="navItem.href">
                            <component :is="navItem.icon" />
                            <span>{{ navItem.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </template>
</template>
