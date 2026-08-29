<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Table from '@/Components/Table.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    consentType: Object,
    consentVersions: Array,
    columns: Object
})
</script>
<template lang="">
    <Head :title="`Versioni ${consentType.name}`" />
    <AuthenticatedLayout section="consenttypes">
        <div class="row">

            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Versioni {{consentType.name}}</h2>
                    <Link :href="route('admin.consent-types.consent-versions.create', props.consentType.id)" class="main-button">Aggiungi versione</Link>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">

                <Table
                    :items="consentVersions"
                    :columns="columns"
                    baseRoute="admin.consent-types.consent-versions"
                    :editableColumns="[]"
                    :parentId="consentType.id"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
