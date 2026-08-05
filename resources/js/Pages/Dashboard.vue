<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AppointmentsTab from "@/Components/Dashboard/AppointmentsTab.vue";
import InvoicesTab from "@/Components/Dashboard/InvoicesTab.vue";
import DoctorsTab from "@/Components/Dashboard/DoctorsTab.vue";
import PatientsTab from "@/Components/Dashboard/PatientsTab.vue";

import { useConfigStore } from "@/stores/main";
import RecentActivitiesTab from "@/Components/Dashboard/RecentActivitiesTab.vue";
import PendingOperationsTab from "@/Components/Dashboard/PendingOperationsTab.vue";

const { user, hasRole } = useConfigStore();

const props = defineProps({
    invoiceStats: {
        type: Object,
        default: () => []
    },
    doctors: {
        type: Array,
        default: () => []
    },
    patients: {
        type: Array,
        default: () => []
    },
    invoiceChart: {
        type: Array,
        default: () => []
    },
    recentActivities: {
        type: Array,
        default: () => []
    },
    pendingOperations: {
        type: Array,
        default: () => []
    },
    appointments: Array,
})

</script>


<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout section="dashboard">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Benvenuto {{ user.name }} {{ user.surname }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div>
                    <AppointmentsTab :appointments="appointments" />
                </div>
                <div class="mt-4">
                    <DoctorsTab v-if="hasRole('admin') || hasRole('secretary')" :doctors="doctors" />
                </div>
                <div class="mt-4">
                    <PatientsTab :patients="patients" />
                </div>
            </div>
            <div class="col-12" :class="hasRole('admin') || hasRole('secretary') ? 'col-md-3' : 'col-md-6'">
                <div class="row gy-4">
                    <div class="col-12">
                        <RecentActivitiesTab :recentActivities="recentActivities" />
                    </div>
                    <div class="col-12">
                        <PendingOperationsTab v-if="hasRole('admin') || hasRole('secretary')"
                            :pendingOperations="pendingOperations" />
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="row">
                    <div class="col-12">

                        <InvoicesTab v-if="hasRole('admin') || hasRole('secretary')" :invoiceStats="invoiceStats"
                            :invoiceChart="invoiceChart" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
