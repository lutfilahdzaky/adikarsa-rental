<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { LayoutGrid, Truck, Users, ShoppingCart, Clock } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();
const auth = computed(() => page.props.auth as { user?: { role?: string } } | undefined);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Heavy Equipments',
            href: '/heavy-equipments',
            icon: Truck,
        },
        {
            title: 'Customers',
            href: '/customers',
            icon: Users,
        },
        {
            title: 'New Rental',
            href: '/rentals/create',
            icon: ShoppingCart,
        },
        {
            title: 'History',
            href: '/history',
            icon: Clock,
        },
    ];

    return auth.value?.user?.role === 'administrator'
        ? items
        : items.filter((item) => item.title === 'New Rental' || item.title === 'History');
});

const homeLink = computed(() => {
    return auth.value?.user?.role === 'administrator'
        ? dashboard()
        : '/rentals/create';
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="homeLink">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
