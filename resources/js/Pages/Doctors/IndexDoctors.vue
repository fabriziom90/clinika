<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    doctors: Array,
    columns: Object,
});
</script>
<template lang="">
    <Head title="Dottori"></Head>
    <AuthenticatedLayout section="doctors">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco dottori</h2>
                <Link :href="route('admin.doctors.create')" class="main-button"
                    >Aggiungi dottore</Link
                >
            </div>
        </div>
        <Table
            :items="doctors"
            :columns="columns"
            baseRoute="admin.doctors"
            :editableColumns="[]"
        >
        </Table>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
