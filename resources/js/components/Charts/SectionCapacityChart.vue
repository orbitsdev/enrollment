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
    data: Record<string, { enrolled: number; max: number }>;
}

const props = defineProps<Props>();

const chartData = computed(() => ({
    labels: Object.keys(props.data),
    datasets: [
        {
            label: 'Enrolled',
            data: Object.values(props.data).map((v) => v.enrolled),
            backgroundColor: '#e1713e', // chart-1: oklch(0.646 0.222 41.116)
            borderRadius: 4,
        },
        {
            label: 'Max Capacity',
            data: Object.values(props.data).map((v) => v.max),
            backgroundColor: '#d4d4d8', // neutral-300 equivalent
            borderRadius: 4,
        },
    ],
}));

const chartOptions = {
    indexAxis: 'y' as const,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        title: {
            display: true,
            text: 'Section Capacity',
            font: { size: 14, weight: 'bold' as const },
        },
        legend: {
            position: 'bottom' as const,
        },
    },
    scales: {
        x: {
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
    <div class="h-[300px] w-full" :style="{ minHeight: Object.keys(data).length * 40 + 100 + 'px' }">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
