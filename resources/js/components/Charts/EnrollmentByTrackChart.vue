<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface Props {
    data: Record<string, number>;
}

const props = defineProps<Props>();

// Theme-aligned chart colors (oklch-equivalent hex values matching --chart-1 through --chart-5)
const chartColors = [
    '#e1713e', // chart-1: oklch(0.646 0.222 41.116)
    '#1a9a82', // chart-2: oklch(0.6 0.118 184.704)
    '#2c526b', // chart-3: oklch(0.398 0.07 227.392)
    '#d4b140', // chart-4: oklch(0.828 0.189 84.429)
    '#d49040', // chart-5: oklch(0.769 0.188 70.08)
    '#8b5cf6', // extra: purple fallback
];

const chartData = computed(() => ({
    labels: Object.keys(props.data),
    datasets: [
        {
            label: 'Enrolled Students',
            data: Object.values(props.data),
            backgroundColor: Object.keys(props.data).map(
                (_, i) => chartColors[i % chartColors.length],
            ),
            borderRadius: 6,
            maxBarThickness: 60,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        title: {
            display: true,
            text: 'Enrollment by Track',
            font: { size: 14, weight: 'bold' as const },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                precision: 0,
            },
        },
    },
};
</script>

<template>
    <div class="h-[300px] w-full">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
