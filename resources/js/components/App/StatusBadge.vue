<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { BadgeVariants } from '@/components/ui/badge';

const props = defineProps<{
    status: string;
}>();

const statusConfig = computed<{
    variant: BadgeVariants['variant'];
    label: string;
}>(() => {
    const s = props.status.toLowerCase();

    const map: Record<
        string,
        { variant: BadgeVariants['variant']; label: string }
    > = {
        // Enrollment statuses
        pending: { variant: 'secondary', label: 'Pending' },
        enrolled: { variant: 'default', label: 'Enrolled' },
        dropped: { variant: 'destructive', label: 'Dropped' },
        transferred: { variant: 'outline', label: 'Transferred' },

        // Student statuses
        active: { variant: 'default', label: 'Active' },
        inactive: { variant: 'secondary', label: 'Inactive' },
        graduated: { variant: 'outline', label: 'Graduated' },

        // Grade remarks
        passed: { variant: 'default', label: 'Passed' },
        failed: { variant: 'destructive', label: 'Failed' },

        // Generic
        open: { variant: 'default', label: 'Open' },
        closed: { variant: 'secondary', label: 'Closed' },
    };

    return map[s] ?? { variant: 'secondary', label: props.status };
});
</script>

<template>
    <Badge :variant="statusConfig.variant">
        {{ statusConfig.label }}
    </Badge>
</template>
