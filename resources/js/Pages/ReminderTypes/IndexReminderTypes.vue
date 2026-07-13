<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Table from '@/Components/Table.vue';
import { Link, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import { useConfigStore } from '@/stores/main';

const { hasPermission } = useConfigStore();

const props = defineProps({
    reminders: Array,
    columns: Object
})

</script>


<template>

    <Head title="Metodi di reminder" />
    <AuthenticatedLayout section="remindertypes">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco metodi reminder</h2>
                <Link :href="route('admin.reminder-types.create')" class="main-button"
                    v-if="hasPermission('patient.create')">
                    Aggiungi tipologia di promemoria</Link>
            </div>
        </div>
        <Table :items="reminders" :columns="columns" baseRoute="admin.reminder-types" :editableColumns="[]" />
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>
