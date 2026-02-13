<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Edit2, Layers, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import StrandFormDialog from '@/components/App/StrandFormDialog.vue';
import TrackFormDialog from '@/components/App/TrackFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Strand, Track } from '@/types';

const props = defineProps<{
    tracks: Track[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum' },
    { title: 'Tracks & Strands' },
];

// Track expand/collapse state
const expandedTracks = ref<Set<number>>(new Set(props.tracks.map(t => t.id)));

function toggleTrack(trackId: number) {
    if (expandedTracks.value.has(trackId)) {
        expandedTracks.value.delete(trackId);
    } else {
        expandedTracks.value.add(trackId);
    }
}

// Track dialog state
const trackDialogOpen = ref(false);
const editingTrack = ref<Track | null>(null);

function openCreateTrackDialog() {
    editingTrack.value = null;
    trackDialogOpen.value = true;
}

function openEditTrackDialog(track: Track) {
    editingTrack.value = track;
    trackDialogOpen.value = true;
}

// Toggle track active status
function toggleTrackActive(trackId: number, currentState: boolean) {
    router.put(
        `/curriculum/tracks/${trackId}/toggle-active`,
        { is_active: !currentState },
        { preserveScroll: true },
    );
}

// Strand dialog state
const strandDialogOpen = ref(false);
const editingStrand = ref<Strand | null>(null);
const strandParentTrackId = ref<number | undefined>(undefined);

function openCreateStrandDialog(trackId: number) {
    editingStrand.value = null;
    strandParentTrackId.value = trackId;
    strandDialogOpen.value = true;
}

function openEditStrandDialog(strand: Strand) {
    editingStrand.value = strand;
    strandDialogOpen.value = true;
}

// Toggle strand active status
function toggleStrandActive(strandId: number, currentState: boolean) {
    router.put(
        `/curriculum/strands/${strandId}/toggle-active`,
        { is_active: !currentState },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Tracks & Strands" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PageHeader
                title="Tracks & Strands"
                description="Manage academic tracks and their strands."
            >
                <template #actions>
                    <Button @click="openCreateTrackDialog">
                        <Plus class="mr-2 size-4" />
                        Add Track
                    </Button>
                </template>
            </PageHeader>

            <!-- Tracks List -->
            <div v-if="tracks.length === 0" class="py-12 text-center text-muted-foreground">
                No tracks found. Create one to get started.
            </div>

            <div class="space-y-4">
                <Card v-for="track in tracks" :key="track.id">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div
                                class="flex cursor-pointer items-center gap-3"
                                @click="toggleTrack(track.id)"
                            >
                                <component
                                    :is="expandedTracks.has(track.id) ? ChevronDown : ChevronRight"
                                    class="size-5 text-muted-foreground"
                                />
                                <div>
                                    <div class="flex items-center gap-2">
                                        <CardTitle>{{ track.name }}</CardTitle>
                                        <Badge variant="outline">{{ track.code }}</Badge>
                                        <Badge
                                            :variant="track.is_active ? 'default' : 'secondary'"
                                        >
                                            {{ track.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ track.strands?.length ?? 0 }} strand(s)
                                    </p>
                                </div>
                            </div>

                            <!-- Track Actions -->
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click.stop="openEditTrackDialog(track)"
                                >
                                    <Edit2 class="size-4" />
                                </Button>
                                <div class="flex items-center gap-2">
                                    <Label
                                        :for="`track-active-${track.id}`"
                                        class="text-sm text-muted-foreground"
                                    >
                                        Active
                                    </Label>
                                    <Switch
                                        :id="`track-active-${track.id}`"
                                        :checked="track.is_active"
                                        @update:checked="toggleTrackActive(track.id, track.is_active)"
                                    />
                                </div>
                            </div>
                        </div>
                    </CardHeader>

                    <!-- Expanded Strands Section -->
                    <CardContent v-show="expandedTracks.has(track.id)">
                        <div class="space-y-4">
                            <!-- Add Strand Button -->
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-muted-foreground">
                                    Strands
                                </h4>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openCreateStrandDialog(track.id)"
                                >
                                    <Plus class="mr-1 size-3" />
                                    Add Strand
                                </Button>
                            </div>

                            <!-- Strands Table -->
                            <div
                                v-if="track.strands && track.strands.length > 0"
                                class="rounded-md border"
                            >
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Name</TableHead>
                                            <TableHead class="w-[120px]">Code</TableHead>
                                            <TableHead class="w-[100px]">Status</TableHead>
                                            <TableHead class="w-[100px]">Subjects</TableHead>
                                            <TableHead class="w-[150px] text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="strand in track.strands"
                                            :key="strand.id"
                                        >
                                            <TableCell class="font-medium">
                                                {{ strand.name }}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">{{ strand.code }}</Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    :variant="strand.is_active ? 'default' : 'secondary'"
                                                >
                                                    {{ strand.is_active ? 'Active' : 'Inactive' }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div class="flex items-center gap-1">
                                                    <Layers class="size-3 text-muted-foreground" />
                                                    {{ (strand as any).subjects_count ?? strand.subjects?.length ?? 0 }}
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        @click="openEditStrandDialog(strand)"
                                                    >
                                                        <Edit2 class="size-3" />
                                                    </Button>
                                                    <Switch
                                                        :checked="strand.is_active"
                                                        @update:checked="toggleStrandActive(strand.id, strand.is_active)"
                                                    />
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>

                            <p
                                v-else
                                class="py-4 text-center text-sm text-muted-foreground"
                            >
                                No strands yet. Add one to get started.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <TrackFormDialog
            v-model:open="trackDialogOpen"
            :track="editingTrack"
        />

        <StrandFormDialog
            v-model:open="strandDialogOpen"
            :strand="editingStrand"
            :track-id="strandParentTrackId"
        />
    </AppLayout>
</template>
