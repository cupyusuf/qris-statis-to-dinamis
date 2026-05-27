<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, QrCode, ShieldCheck, Sparkles } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="border-b border-sidebar-border/70 p-4">
            <Link
                :href="dashboard()"
                class="block rounded-3xl border border-sidebar-border/70 bg-sidebar/80 p-3 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <AppLogo />
            </Link>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="gap-3 border-t border-sidebar-border/70 p-4">
            <div class="rounded-3xl border border-sidebar-border/70 bg-sidebar/80 p-4">
                <div class="flex items-center gap-2 text-sm font-medium text-sidebar-foreground">
                    <Sparkles class="size-4 text-primary" />
                    QRIS workspace
                </div>
                <p class="mt-2 text-xs leading-5 text-muted-foreground">
                    Kelola merchant, payload, dan tampilan publik dari satu tempat.
                </p>
                <div class="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                    <ShieldCheck class="size-4 text-primary" />
                    Aktifkan profil untuk halaman publik
                </div>
                <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                    <QrCode class="size-4 text-primary" />
                    Scan QR untuk isi payload otomatis
                </div>
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
