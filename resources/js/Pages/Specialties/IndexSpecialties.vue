<script setup>
import { ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";

const props = defineProps({
    specialties: Array,
    columns: Object,
});

const localSpecialties = ref([...props.specialties]);

const handleInlineUpdate = (updatedData) => {
    localSpecialties.value = updatedData;
};
</script>

<template>
    <Head title="Specializzazioni" />
    <AuthenticatedLayout section="specialties">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco specializzazioni</h2>
                <Link :href="route('admin.specialties.create')" class="main-button">Aggiungi specializzazione</Link>
            </div>

            

            <Table
                :items="localSpecialties"
                :columns="columns"
                baseRoute="admin.specialties"
                :editableColumns="[]"
                @updated="handleInlineUpdate"
            />
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped>
@use "../../../scss/app.scss";
</style>
