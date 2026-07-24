<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import Table from '@/Components/Table.vue';


    const props = defineProps({
        patient: Object,
        patientConsents: Array,
        columns: Object
    })
</script>

<template>
    <Head title="Consensi pazienti"/>
    <AuthenticatedLayout section="patients">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Elenco consensi paziente {{ patient.name }} {{ patient.surname }}</h2>
                    <Link :href="route('admin.patient.consents.create', patient.id)" class="main-button">Inserisci consensi</Link>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <Table
                    :items="patientConsents"
                    :columns="columns"
                    baseRoute="admin.patient.consents"
                    :editableColumns="[]"
                    :parentId="patient.id"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>

</style>
