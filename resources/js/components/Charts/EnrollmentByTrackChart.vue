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

const chartColors = [
    'rgba(59, 130, 246, 0.8)',
    'rgba(16, 185, 129, 0.8)',
    'rgba(245, 158, 11, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(139, 92, 246, 0.8)',
    'rgba(236, 72, 153, 0.8)',
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
