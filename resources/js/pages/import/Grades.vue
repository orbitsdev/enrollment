<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Upload, CheckCircle, XCircle, Loader2 } from 'lucide-vue-next';

interface ValidRow {
    lrn: string;
    subject_code: string;
    midterm: number | null;
    finals: number | null;
    student_id: number;
    subject_id: number;
    student_name: string;
    subject_name: string;
}

interface InvalidRow {
    row: number;
    data: { lrn: string; subject_code: string; midterm: number | null; finals: number | null };
    errors: Record<string, string[]>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Import Data', href: '/import' },
    { title: 'Import Grades', href: '/import/grades' },
];

const step = ref<1 | 2 | 3>(1);
const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const importing = ref(false);
const validRows = ref<ValidRow[]>([]);
const invalidRows = ref<InvalidRow[]>([]);
const importResult = ref<{ imported: number; errors: any[] } | null>(null);

async function handleFileUpload() {
    const file = fileInput.value?.files?.[0];
    if (!file) return;

    uploading.value = true;

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch('/import/grades/upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            toast.error(data.message ?? 'Upload failed');
            return;
        }

        validRows.value = data.valid;
        invalidRows.value = data.invalid;
        step.value = 2;
    } catch (e) {
        toast.error('Upload failed. Please try again.');
    } finally {
        uploading.value = false;
    }
}

async function confirmImport() {
    importing.value = true;

    try {
        const response = await fetch('/import/grades/confirm', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                grades: validRows.value.map((r) => ({
                    student_id: r.student_id,
                    subject_id: r.subject_id,
                    midterm: r.midterm,
                    finals: r.finals,
                })),
            }),
        });

        const data = await response.json();
        importResult.value = data;
        step.value = 3;
    } catch (e) {
        toast.error('Import failed. Please try again.');
    } finally {
        importing.value = false;
    }
}

function reset() {
    step.value = 1;
    validRows.value = [];
    invalidRows.value = [];
    importResult.value = null;
    if (fileInput.value) fileInput.value.value = '';
}
</script>

<template>
    <Head title="Import Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">Import Grades</h1>
                <p class="text-muted-foreground">Upload, preview, and import grade data from Excel.</p>
            </div>

            <!-- Steps Indicator -->
            <div class="mb-6 flex items-center gap-4">
                <div
                    v-for="s in [1, 2, 3]"
                    :key="s"
                    class="flex items-center gap-2"
                >
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium"
                        :class="step >= s ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                    >
                        {{ s }}
                    </div>
                    <span class="text-sm" :class="step >= s ? 'font-medium' : 'text-muted-foreground'">
                        {{ s === 1 ? 'Upload' : s === 2 ? 'Preview' : 'Import' }}
                    </span>
                    <div v-if="s < 3" class="mx-2 h-px w-8 bg-border" />
                </div>
            </div>

            <!-- Step 1: Upload -->
            <Card v-if="step === 1">
                <CardHeader>
                    <CardTitle class="text-base">Upload Excel File</CardTitle>
                    <CardDescription>Select an .xlsx, .xls, or .csv file containing grade data.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col items-center gap-4 rounded-lg border-2 border-dashed p-8">
                        <Upload class="h-10 w-10 text-muted-foreground" />
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="text-sm"
                            @change="handleFileUpload"
                            :disabled="uploading"
                        />
                        <div v-if="uploading" class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Loader2 class="h-4 w-4 animate-spin" />
                            Parsing file...
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Step 2: Preview -->
            <template v-if="step === 2">
                <div class="mb-4 flex items-center gap-4">
                    <Badge variant="default">{{ validRows.length }} valid rows</Badge>
                    <Badge v-if="invalidRows.length > 0" variant="destructive">{{ invalidRows.length }} invalid rows</Badge>
                </div>

                <!-- Valid Rows -->
                <Card v-if="validRows.length > 0" class="mb-4">
                    <CardHeader>
                        <CardTitle class="text-base text-green-600">
                            <CheckCircle class="mr-2 inline h-4 w-4" />
                            Valid Rows ({{ validRows.length }})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="max-h-[400px] overflow-auto">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 bg-background">
                                    <tr class="border-b">
                                        <th class="pb-2 pr-3 text-left font-medium">LRN</th>
                                        <th class="pb-2 pr-3 text-left font-medium">Student</th>
                                        <th class="pb-2 pr-3 text-left font-medium">Subject</th>
                                        <th class="pb-2 pr-3 text-center font-medium">Midterm</th>
                                        <th class="pb-2 text-center font-medium">Finals</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, i) in validRows" :key="i" class="border-b last:border-0">
                                        <td class="py-1.5 pr-3 font-mono text-xs">{{ row.lrn }}</td>
                                        <td class="py-1.5 pr-3">{{ row.student_name }}</td>
                                        <td class="py-1.5 pr-3">{{ row.subject_code }} - {{ row.subject_name }}</td>
                                        <td class="py-1.5 pr-3 text-center">{{ row.midterm ?? '-' }}</td>
                                        <td class="py-1.5 text-center">{{ row.finals ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Invalid Rows -->
                <Card v-if="invalidRows.length > 0" class="mb-4">
                    <CardHeader>
                        <CardTitle class="text-base text-red-600">
                            <XCircle class="mr-2 inline h-4 w-4" />
                            Invalid Rows ({{ invalidRows.length }}) - Will be skipped
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="max-h-[300px] overflow-auto">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 bg-background">
                                    <tr class="border-b">
                                        <th class="pb-2 pr-3 text-left font-medium">Row</th>
                                        <th class="pb-2 pr-3 text-left font-medium">LRN</th>
                                        <th class="pb-2 pr-3 text-left font-medium">Subject Code</th>
                                        <th class="pb-2 text-left font-medium">Errors</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, i) in invalidRows" :key="i" class="border-b last:border-0 bg-red-50 dark:bg-red-950/20">
                                        <td class="py-1.5 pr-3">{{ row.row }}</td>
                                        <td class="py-1.5 pr-3 font-mono text-xs">{{ row.data.lrn }}</td>
                                        <td class="py-1.5 pr-3">{{ row.data.subject_code }}</td>
                                        <td class="py-1.5">
                                            <ul class="list-inside list-disc text-xs text-red-600">
                                                <template v-for="(msgs, field) in row.errors" :key="field">
                                                    <li v-for="msg in msgs" :key="msg">{{ field }}: {{ msg }}</li>
                                                </template>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex gap-3">
                    <Button @click="reset" variant="outline">Cancel</Button>
                    <Button
                        @click="confirmImport"
                        :disabled="validRows.length === 0 || importing"
                    >
                        <Loader2 v-if="importing" class="mr-2 h-4 w-4 animate-spin" />
                        Import {{ validRows.length }} Grades
                    </Button>
                </div>
            </template>

            <!-- Step 3: Results -->
            <Card v-if="step === 3">
                <CardHeader>
                    <CardTitle class="text-base">Import Complete</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-center gap-3 text-green-600">
                        <CheckCircle class="h-6 w-6" />
                        <span class="text-lg font-medium">
                            {{ importResult?.imported ?? 0 }} grades imported successfully
                        </span>
                    </div>

                    <div v-if="importResult?.errors && importResult.errors.length > 0" class="mt-4">
                        <p class="mb-2 text-sm font-medium text-red-600">
                            {{ importResult.errors.length }} rows had errors:
                        </p>
                        <ul class="list-inside list-disc text-sm text-red-600">
                            <li v-for="(err, i) in importResult.errors" :key="i">
                                Row {{ err.row }}: {{ err.error }}
                            </li>
                        </ul>
                    </div>

                    <Button @click="reset">Import More</Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
