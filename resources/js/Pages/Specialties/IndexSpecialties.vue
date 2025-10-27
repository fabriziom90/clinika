<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";

import { ref } from "vue";

const props = defineProps({
    specialties: Array,
    columns: Object,
});
</script>
<template lang="">
    <Head title="Specializzazioni"></Head>
    <AuthenticatedLayout section="specialties">
        <div>
            <h2>Elenco specializzazioni</h2>
            <Table
                :items="specialties"
                :columns="columns"
                baseRoute="specialties"
            ></Table>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
