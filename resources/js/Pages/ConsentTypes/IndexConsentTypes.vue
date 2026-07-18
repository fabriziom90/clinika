<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import Table from '@/Components/Table.vue';

    const props = defineProps({
        consentTypes: Array,
        columns: Object

    });
</script>
<template lang="">
    <Head title="Moduli consenso"/>
    <AuthenticatedLayout section="consenttypes">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco tipologie consenso</h2>
                <Link :href="route('admin.consent-types.create')" class="main-button"
                    >Aggiungi Tipologia consenso</Link
                >
            </div>
        </div>
        <Table
            :items="consentTypes"
            :columns="columns"
            baseRoute="admin.consent-types"
            :editableColumns="[]"
        />
    </AuthenticatedLayout>
</template>
<style lang="">

</style>
