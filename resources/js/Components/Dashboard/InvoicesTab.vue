<script setup>
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    invoiceStats: {
        type: Object,
        required: true,
    },
    invoiceChart: {
        type: Array,
        required: true,
    },
});

const currentChart = ref(0);

const charts = [
    'Stato fatture',
    'Fatturato vs incassato',
    'Fatturato mensile',
    'Tasso di incasso',
];

const previousChart = () => {
    currentChart.value =
        currentChart.value === 0
            ? charts.length - 1
            : currentChart.value - 1;
};

const nextChart = () => {
    currentChart.value =
        currentChart.value === charts.length - 1
            ? 0
            : currentChart.value + 1;
};

const currencyFormatter = (value) => {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
};

const currentChartName = computed(() => charts[currentChart.value]);

const nextChartName = computed(() => {
    return charts[
        currentChart.value === charts.length - 1
            ? 0
            : currentChart.value + 1
    ];
});

const previousChartName = computed(() => {
    return charts[
        currentChart.value === 0
            ? charts.length - 1
            : currentChart.value - 1
    ];
});

/*
|--------------------------------------------------------------------------
| INVOICES STATE
|--------------------------------------------------------------------------
*/

const statusChartOptions = {
    chart: {
        type: 'donut',
    },
    labels: [
        'Pagate',
        'Da incassare',
        'Cancellate',
        'Bozze',
    ],
    legend: {
        position: 'bottom',
    },
    dataLabels: {
        enabled: true,
        formatter: (value) => `${value.toFixed(1)}%`,
    },
    tooltip: {
        y: {
            formatter: (value) => currencyFormatter(value),
        },
    },
};

const statusSeries = computed(() => [
    Number(props.invoiceStats.paid_total),
    Number(props.invoiceStats.outstanding_total),
    Number(props.invoiceStats.cancelled_total),
    Number(props.invoiceStats.draft_total),
]);

/*
|--------------------------------------------------------------------------
| MONTHLY DATA
|--------------------------------------------------------------------------
*/

const months = computed(() => props.invoiceChart.map(item => item.label));

const issuedSeries = computed(() =>
    props.invoiceChart.map(item => Number(item.issued))
);

const paidSeries = computed(() =>
    props.invoiceChart.map(item => Number(item.paid))
);

const collectionRateSeries = computed(() =>
    props.invoiceChart.map(item => Number(item.collection_rate))
);

/*
|--------------------------------------------------------------------------
| REVENUE VS PAYMENTS
|--------------------------------------------------------------------------
*/

const revenueComparisonOptions = computed(() => ({
    chart: {
        type: 'line',
        toolbar: {
            show: false,
        },
    },
    xaxis: {
        categories: months.value,
    },
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    legend: {
        position: 'top',
    },
    tooltip: {
        y: {
            formatter: (value) => currencyFormatter(value),
        },
    },
}));

const revenueComparisonSeries = computed(() => [
    {
        name: 'Fatturato emesso',
        data: issuedSeries.value,
    },
    {
        name: 'Incassato',
        data: paidSeries.value,
    },
]);

/*
|--------------------------------------------------------------------------
| MONTHLY REVENUES
|--------------------------------------------------------------------------
*/

const monthlyRevenueOptions = computed(() => ({
    chart: {
        type: 'bar',
        toolbar: {
            show: false,
        },
    },
    xaxis: {
        categories: months.value,
    },
    plotOptions: {
        bar: {
            borderRadius: 4,
            columnWidth: '50%',
        },
    },
    tooltip: {
        y: {
            formatter: (value) => currencyFormatter(value),
        },
    },
}));

const monthlyRevenueSeries = computed(() => [
    {
        name: 'Fatturato',
        data: issuedSeries.value,
    },
]);

/*
|--------------------------------------------------------------------------
| COLLECTION RATE
|--------------------------------------------------------------------------
*/

const collectionRateOptions = computed(() => ({
    chart: {
        type: 'line',
        toolbar: {
            show: false,
        },
    },
    xaxis: {
        categories: months.value,
    },
    yaxis: {
        min: 0,
        max: 100,
        labels: {
            formatter: (value) => `${value}%`,
        },
    },
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    tooltip: {
        y: {
            formatter: (value) => `${value.toFixed(2)}%`,
        },
    },
}));

const collectionRateChartSeries = computed(() => [
    {
        name: 'Tasso di incasso',
        data: collectionRateSeries.value,
    },
]);
</script>

<template>
    <div class="card invoice-chart-card">
        <div class="card-header bg-main-red-light">
            <div class="chart-navigation">
                <button type="button" class="chart-navigation__arrow" @click="previousChart"
                    :title="`Precedente: ${previousChartName}`">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="chart-navigation__title">
                    <h2 class="text-white mb-0">
                        {{ currentChartName }}
                    </h2>
                </div>

                <button type="button" class="chart-navigation__arrow" @click="nextChart"
                    :title="`Successivo: ${nextChartName}`">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="chart-selector">
                <select v-model="currentChart">
                    <option v-for="(chart, index) in charts" :key="chart" :value="index">
                        {{ chart }}
                    </option>
                </select>
            </div>

            <!-- INVOICES STATE -->
            <VueApexCharts v-if="currentChart === 0" type="donut" height="350" :options="statusChartOptions"
                :series="statusSeries" />

            <!-- REVENUES VS PAYMENTS -->
            <VueApexCharts v-else-if="currentChart === 1" type="line" height="350" :options="revenueComparisonOptions"
                :series="revenueComparisonSeries" />

            <!-- MONTHLY REVENUES -->
            <VueApexCharts v-else-if="currentChart === 2" type="bar" height="350" :options="monthlyRevenueOptions"
                :series="monthlyRevenueSeries" />

            <!-- COLLECTION RATE -->
            <VueApexCharts v-else-if="currentChart === 3" type="line" height="350" :options="collectionRateOptions"
                :series="collectionRateChartSeries" />
        </div>
    </div>
</template>

<style lang="scss" scoped>
@use '../../../scss/app.scss' as *;

.chart-navigation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.chart-navigation__title {
    flex: 1;
    text-align: center;
}

.chart-navigation__arrow {
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: white;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;

    &:hover {
        background-color: rgba(255, 255, 255, 0.15);
    }
}

.chart-selector {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1rem;

    select {
        min-width: 220px;
        padding: 0.45rem 2rem 0.45rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: white;
        cursor: pointer;
    }
}
</style>