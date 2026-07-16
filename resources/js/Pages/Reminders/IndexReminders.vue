<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import { formatDate } from "@/utilities/formatDateFunction";

const props = defineProps({
    reminders: {
        type: Object,
        required: true,
    },
    reminderTypes: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        default: () => [],
    },
});

const filters = ref({
    status: props.filters.status ?? "",
    reminder_type_id: props.filters.reminder_type_id ?? "",
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    patient: props.filters.patient ?? "",
});

const applyFilters = () => {
    router.get(route("admin.reminders.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

function statusLabel(status) {
    const labels = {
        pending: "In attesa",
        sent: "Inviato",
        failed: "Fallito",
    };

    return labels[status] ?? status;
}

function statusClass(status) {
    return {
        sent: "success",
        failed: "danger",
        pending: "warning",
    }[status];
}
</script>
<template lang="">
    <Head title="Logs Promemoria" />
    <AuthenticatedLayout section="reminders">
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Paziente</th>
                        <th>Appuntamento</th>
                        <th>Tipo</th>
                        <th>Invio previsto</th>
                        <th>Stato</th>
                        <th>Inviato il</th>
                        <th>Strumenti</th>
                    </tr>
                    <tr>
                        <th>
                            <input v-model="filters.patient" class="form-control column-filter" placeholder="Filtra..." @keyup.enter="applyFilters" />
                        </th>
                        <th></th>
                        <th>
                            <select v-model="filters.reminder_type_id" class="form-control" @change="applyFilters">
                                <option value="">Tutti</option>
                                <option v-for="type in reminderTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </th>
                        <th>
                            <input v-model="filters.date_from" type="date" class="form-control column-filter"/>
                            <input v-model="filters.date_to" type="date" class="form-control column-filter"/>
                        </th>
                        <th>
                            <select v-model="filters.status" class="form-control" @change="applyFilters">
                                <option value="">Tutti</option>
                                <option value="pending">In attesa</option>
                                <option value="sent">Inviati</option>
                                <option value="failed">Falliti</option>
                            </select>
                        </th>
                        <th></th>
                        <th>
                            <button class="btn-blue" @click="applyFilters">
                                <i class="fas fa-search"></i>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="reminder in reminders.data" :key="reminder.id">
                        <td>
                            {{ reminder.patient.name }}
                            {{ reminder.patient.surname }}
                        </td>
                        <td>
                            {{ formatDate(reminder.appointment.start_time) }}
                        </td>
                        <td>
                            {{ reminder.reminder_type.name }}
                        </td>
                        <td>
                            {{ formatDate(reminder.scheduled_for) }}
                        </td>
                        <td>
                            <span class="status-badge" :class="statusClass(reminder.status)">
                                {{ statusLabel(reminder.status) }}
                            </span>
                        </td>
                        <td>
                            {{ reminder.sent_at ? formatDate(reminder.sent_at) : "-" }}
                        </td>
                        <td class="actions">
                            <Link class="show-button" :href="route('admin.reminders.show', reminder.id)">
                                <i class="fas fa-eye"></i>
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!reminders.data.length">
                        <td colspan="7" class="text-center">
                            Nessun risultato
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="pagination">
                <Link
                    v-for="link in reminders.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    preserve-scroll
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
