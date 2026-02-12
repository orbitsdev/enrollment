<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Edit2, Layers, Plus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import PageHeader from '@/components/App/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
import type { BreadcrumbItem, Track } from '@/types';

defineProps<{
    tracks: Track[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum' },
    { title: 'Tracks & Strands' },
];

// Track expand/collapse state
const expandedTracks = ref<Set<number>>(new Set());

function toggleTrack(trackId: number) {
    if (expandedTracks.value.has(trackId)) {
        expandedTracks.value.delete(trackId);
    } else {
        expandedTracks.value.add(trackId);
    }
}

// Create track form
const showCreateTrack = ref(false);
const trackForm = useForm({
    name: '',
    code: '',
});

function createTrack() {
    trackForm.post('/curriculum/tracks', {
        preserveScroll: true,
        onSuccess: () => {
            trackForm.reset();
            showCreateTrack.value = false;
        },
    });
}

// Edit track inline
const editingTrackId = ref<number | null>(null);
const editTrackForm = useForm({
    name: '',
    code: '',
});

function startEditTrack(track: Track) {
    editingTrackId.value = track.id;
    editTrackForm.name = track.name;
    editTrackForm.code = track.code;
}

function saveEditTrack(trackId: number) {
    editTrackForm.put(`/curriculum/tracks/${trackId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTrackId.value = null;
            editTrackForm.reset();
        },
    });
}

function cancelEditTrack() {
    editingTrackId.value = null;
    editTrackForm.reset();
    editTrackForm.clearErrors();
}

// Toggle track active status
function toggleTrackActive(trackId: number, currentState: boolean) {
    router.put(
        `/curriculum/tracks/${trackId}/toggle-active`,
        { is_active: !currentState },
        { preserveScroll: true },
    );
}

// Create strand form
const showCreateStrandFor = ref<number | null>(null);
const strandForm = useForm({
    name: '',
    code: '',
});

function createStrand(trackId: number) {
    strandForm.post(`/curriculum/tracks/${trackId}/strands`, {
        preserveScroll: true,
        onSuccess: () => {
            strandForm.reset();
            showCreateStrandFor.value = null;
        },
    });
}

// Edit strand inline
const editingStrandId = ref<number | null>(null);
const editStrandForm = useForm({
    name: '',
    code: '',
});

function startEditStrand(strand: { id: number; name: string; code: string }) {
    editingStrandId.value = strand.id;
    editStrandForm.name = strand.name;
    editStrandForm.code = strand.code;
}

function saveEditStrand(strandId: number) {
    editStrandForm.put(`/curriculum/strands/${strandId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingStrandId.value = null;
            editStrandForm.reset();
        },
    });
}

function cancelEditStrand() {
    editingStrandId.value = null;
    editStrandForm.reset();
    editStrandForm.clearErrors();
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
                    <Button @click="showCreateTrack = !showCreateTrack">
                        <Plus class="mr-2 size-4" />
                        Add Track
                    </Button>
                </template>
            </PageHeader>

            <!-- Create Track Form -->
            <Card v-if="showCreateTrack">
                <CardHeader>
                    <CardTitle>New Track</CardTitle>
                </CardHeader>
                <CardContent>
                    <form
                        class="flex items-end gap-4"
                        @submit.prevent="createTrack"
                    >
                        <div class="flex-1 space-y-2">
                            <Label for="track_name">Name</Label>
                            <Input
                                id="track_name"
                                v-model="trackForm.name"
                                type="text"
                                placeholder="e.g. Academic Track"
                            />
                            <InputError :message="trackForm.errors.name" />
                        </div>
                        <div class="w-40 space-y-2">
                            <Label for="track_code">Code</Label>
                            <Input
                                id="track_code"
                                v-model="trackForm.code"
                                type="text"
                                placeholder="e.g. ACAD"
                            />
                            <InputError :message="trackForm.errors.code" />
                        </div>
                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                :disabled="trackForm.processing"
                            >
                                Create
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="
                                    showCreateTrack = false;
                                    trackForm.reset();
                                    trackForm.clearErrors();
                                "
                            >
                                <X class="size-4" />
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Tracks List -->
            <div v-if="tracks.length === 0" class="py-12 text-center text-muted-foreground">
                No tracks found. Create one to get started.
            </div>

            <div class="space-y-4">
                <Card v-for="track in tracks" :key="track.id">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <!-- Track Info / Edit -->
                            <div
                                v-if="editingTrackId !== track.id"
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

                            <!-- Inline Edit Track -->
                            <form
                                v-else
                                class="flex flex-1 items-end gap-4"
                                @submit.prevent="saveEditTrack(track.id)"
                            >
                                <div class="flex-1 space-y-2">
                                    <Label>Name</Label>
                                    <Input
                                        v-model="editTrackForm.name"
                                        type="text"
                                    />
                                    <InputError :message="editTrackForm.errors.name" />
                                </div>
                                <div class="w-40 space-y-2">
                                    <Label>Code</Label>
                                    <Input
                                        v-model="editTrackForm.code"
                                        type="text"
                                    />
                                    <InputError :message="editTrackForm.errors.code" />
                                </div>
                                <div class="flex gap-2">
                                    <Button
                                        type="submit"
                                        size="sm"
                                        :disabled="editTrackForm.processing"
                                    >
                                        Save
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="cancelEditTrack"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>

                            <!-- Track Actions -->
                            <div
                                v-if="editingTrackId !== track.id"
                                class="flex items-center gap-2"
                            >
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click.stop="startEditTrack(track)"
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
                            <!-- Add Strand Button & Form -->
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-muted-foreground">
                                    Strands
                                </h4>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="
                                        showCreateStrandFor =
                                            showCreateStrandFor === track.id
                                                ? null
                                                : track.id
                                    "
                                >
                                    <Plus class="mr-1 size-3" />
                                    Add Strand
                                </Button>
                            </div>

                            <!-- Inline Create Strand Form -->
                            <div
                                v-if="showCreateStrandFor === track.id"
                                class="rounded-lg border p-4"
                            >
                                <form
                                    class="flex items-end gap-4"
                                    @submit.prevent="createStrand(track.id)"
                                >
                                    <div class="flex-1 space-y-2">
                                        <Label>Strand Name</Label>
                                        <Input
                                            v-model="strandForm.name"
                                            type="text"
                                            placeholder="e.g. Science, Technology, Engineering, and Mathematics"
                                        />
                                        <InputError :message="strandForm.errors.name" />
                                    </div>
                                    <div class="w-40 space-y-2">
                                        <Label>Code</Label>
                                        <Input
                                            v-model="strandForm.code"
                                            type="text"
                                            placeholder="e.g. STEM"
                                        />
                                        <InputError :message="strandForm.errors.code" />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button
                                            type="submit"
                                            size="sm"
                                            :disabled="strandForm.processing"
                                        >
                                            Create
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="
                                                showCreateStrandFor = null;
                                                strandForm.reset();
                                                strandForm.clearErrors();
                                            "
                                        >
                                            <X class="size-4" />
                                        </Button>
                                    </div>
                                </form>
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
                                            <!-- Name / Inline Edit -->
                                            <template v-if="editingStrandId === strand.id">
                                                <TableCell>
                                                    <div class="space-y-1">
                                                        <Input
                                                            v-model="editStrandForm.name"
                                                            type="text"
                                                            class="h-8"
                                                        />
                                                        <InputError :message="editStrandForm.errors.name" />
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div class="space-y-1">
                                                        <Input
                                                            v-model="editStrandForm.code"
                                                            type="text"
                                                            class="h-8"
                                                        />
                                                        <InputError :message="editStrandForm.errors.code" />
                                                    </div>
                                                </TableCell>
                                                <TableCell colspan="2" />
                                                <TableCell class="text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <Button
                                                            size="sm"
                                                            :disabled="editStrandForm.processing"
                                                            @click="saveEditStrand(strand.id)"
                                                        >
                                                            Save
                                                        </Button>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            @click="cancelEditStrand"
                                                        >
                                                            Cancel
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </template>
                                            <template v-else>
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
                                                            @click="startEditStrand(strand)"
                                                        >
                                                            <Edit2 class="size-3" />
                                                        </Button>
                                                        <Switch
                                                            :checked="strand.is_active"
                                                            @update:checked="toggleStrandActive(strand.id, strand.is_active)"
                                                        />
                                                    </div>
                                                </TableCell>
                                            </template>
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
    </AppLayout>
</template>
