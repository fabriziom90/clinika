<script setup>
import { ref, computed } from "vue";
import { Link, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    logs: {
        type: Object,
        default: () => ({
            data: [],
            prev_page_url: null,
            next_page_url: null,
        }),
    },

    filters: {
        type: Object,
        default: () => ({
            search: "",
        }),
    },
});

const search = ref(props.filters.search || "");

const filteredLogs = computed(() => props.logs.data);

const entityLabels = {
    "App\\Models\\User": "Utente",
    "App\\Models\\Doctor": "Dottore",
    "App\\Models\\Nurse": "Infermiere",
    "App\\Models\\Secretary": "Segretaria",
    "App\\Models\\Patient": "Paziente",
    "App\\Models\\Invoice": "Fattura",
    "App\\Models\\Appointment": "Appuntamento",
    "App\\Models\\Clinic": "Clinica",
};

const getEntityLabel = (type) => {
    return entityLabels[type] || "Elemento";
};

const getActionLabel = (event) => {
    const labels = {
        created: "Creato",
        updated: "Modificato",
        deleted: "Eliminato",
        login: "Accesso effettuato",
        logout: "Disconnessione",
        login_failed: "Accesso fallito",
        viewed: "Visualizzato",
        "status changed": "Stato modificato",
        update: "Aggiornato",
        void: "Annullato",
        show_pdf: "PDF visualizzato",
    };

    return labels[event] || event;
};

const getActionClass = (event) => {
    if (event === "login") {
        return "bg-success";
    }

    if (event === "logout") {
        return "bg-secondary";
    }

    if (event === "login_failed") {
        return "bg-danger";
    }

    if (["created", "updated", "deleted", "update", "status changed"].includes(event)) {
        return "bg-warning text-dark";
    }

    return "bg-primary";
};

const getUserName = (user) => {
    if (!user) {
        return "Sistema";
    }

    return `${user.name} ${user.surname}`.trim();
};
</script>

<template>

    <Head title="Log sistema" />

    <AuthenticatedLayout section="logs">

        <div class="row align-items-center mb-4">
            <div class="col">
                <h2 class="mb-1">
                    Log sistema
                </h2>

                <p class="text-muted mb-0">
                    Storico delle principali attività effettuate nel sistema.
                </p>
            </div>
        </div>

        <div class="card">

            <div class="card-body">

                <div class="mb-4 d-flex">
                    <input v-model="search" type="text" class="form-control me-2"
                        placeholder="Cerca per azione o elemento" />

                    <Link :href="`/admin/audit-logs?search=${encodeURIComponent(search)}`" class="main-button">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>
                        Cerca
                    </Link>
                </div>

                <div v-if="filteredLogs.length" class="table-responsive">

                    <table class="table align-middle">

                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Utente</th>
                                <th>Azione</th>
                                <th>Elemento</th>
                                <th>ID</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr v-for="log in filteredLogs" :key="log.id">

                                <td>
                                    {{ new Date(log.created_at).toLocaleString() }}
                                </td>

                                <td>
                                    {{ getUserName(log.user) }}
                                </td>

                                <td>
                                    <span class="badge" :class="getActionClass(log.event)">
                                        {{ getActionLabel(log.event) }}
                                    </span>
                                </td>

                                <td>
                                    {{ getEntityLabel(log.auditable_type) }}
                                </td>

                                <td>
                                    {{ log.auditable_id ?? "-" }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <div v-else class="text-center py-5">

                    <i class="fa-solid fa-clock-rotate-left fs-1 text-muted mb-3"></i>

                    <p class="text-muted mb-0">
                        Nessuna attività trovata.
                    </p>

                </div>

                <div v-if="props.logs.prev_page_url || props.logs.next_page_url"
                    class="d-flex justify-content-end mt-4">

                    <Link v-if="props.logs.prev_page_url" :href="props.logs.prev_page_url"
                        class="btn btn-secondary me-2">
                        <i class="fa-solid fa-chevron-left me-1"></i>
                        Precedente
                    </Link>

                    <Link v-if="props.logs.next_page_url" :href="props.logs.next_page_url" class="btn btn-secondary">
                        Successiva
                        <i class="fa-solid fa-chevron-right ms-1"></i>
                    </Link>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>
