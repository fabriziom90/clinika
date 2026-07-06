<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    secretaries: Array,
    columns: Object,
});
</script>
<template lang="">
    <Head title="Segretarie"></Head>
    <AuthenticatedLayout section="secretariess">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco segretarie</h2>
                <Link :href="route('admin.secretaries.create')" class="main-button"
                    >Aggiungi segretaria</Link
                >
            </div>
        </div>
        <Table
            :items="secretaries"
            :columns="columns"
            baseRoute="admin.secretaries"
            :editableColumns="[]"
        >
        </Table>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
