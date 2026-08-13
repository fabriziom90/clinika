<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PatientSearch from "@/Components/PatientSearch.vue";
import Table from "@/Components/Table.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { useConfigStore } from "@/stores/main";

const props = defineProps({
    patients: Array,
    columns: Object,
});

const { user, hasPermission, hasRole } = useConfigStore()

</script>
<template lang="">
    <Head title="Pazienti" />
    <AuthenticatedLayout section="patients">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco pazienti</h2>
                <Link :href="route('admin.patients.create')" class="main-button" v-if="hasPermission('patient.create')"
                    >Aggiungi paziente</Link
                >
            </div>
        </div>
        <Table
            v-if="hasRole('admin') || hasRole('secretary')"
            :items="patients"
            :columns="columns"
            baseRoute="admin.patients"
            :editableColumns="[]"
        />
        <PatientSearch v-else />
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
</style>
