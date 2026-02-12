<script setup lang="ts" generic="T extends Record<string, unknown>">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

export interface Column {
    key: string;
    label: string;
    class?: string;
}

defineProps<{
    columns: Column[];
    data: T[];
    emptyMessage?: string;
}>();
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead
                        v-for="column in columns"
                        :key="column.key"
                        :class="column.class"
                    >
                        {{ column.label }}
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="data.length > 0">
                    <TableRow v-for="(row, index) in data" :key="index">
                        <slot name="row" :row="row" :index="index">
                            <TableCell
                                v-for="column in columns"
                                :key="column.key"
                                :class="column.class"
                            >
                                {{ row[column.key] }}
                            </TableCell>
                        </slot>
                    </TableRow>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell
                            :colspan="columns.length"
                            class="h-24 text-center text-muted-foreground"
                        >
                            <slot name="empty">
                                {{ emptyMessage ?? 'No results found.' }}
                            </slot>
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
