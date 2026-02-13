<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    BookOpen,
    CalendarDays,
    ClipboardList,
    FileSpreadsheet,
    GraduationCap,
    LayoutGrid,
    LayoutList,
    Settings,
    Upload,
    User,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import type { NavigationGroup } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();

const iconMap: Record<string, Component> = {
    LayoutGrid,
    Users,
    Settings,
    BookOpen,
    CalendarDays,
    GraduationCap,
    UserCheck,
    LayoutList,
    ClipboardList,
    FileSpreadsheet,
    BarChart3,
    Upload,
    User,
};

function resolveIcon(iconName: string): Component {
    return iconMap[iconName] ?? LayoutGrid;
}

const navigationGroups = computed<NavigationGroup[]>(
    () => (page.props.navigation as NavigationGroup[]) ?? [],
);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup
                v-for="group in navigationGroups"
                :key="group.label"
                class="px-2 py-0"
            >
                <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="item in group.items"
                        :key="item.href"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href" prefetch>
                                <component :is="resolveIcon(item.icon)" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
