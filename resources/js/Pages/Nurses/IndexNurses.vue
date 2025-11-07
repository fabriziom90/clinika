<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    nurses: Array,
    columns: Object,
});
</script>
<template lang="">
    <Head title="Infermieri"></Head>
    <AuthenticatedLayout section="nurses">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco infermieri</h2>
                <Link :href="route('admin.nurses.create')" class="main-button"
                    >Aggiungi infermiere</Link
                >
            </div>
        </div>
        <Table
            :items="nurses"
            :columns="columns"
            baseRoute="admin.nurses"
            :editableColumns="[]"
        >
        </Table>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
