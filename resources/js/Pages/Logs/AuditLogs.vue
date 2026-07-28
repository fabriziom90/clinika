<script setup>
import { ref, computed } from "vue";
import { usePage, Link, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    logs: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");

const filteredLogs = computed(() => props.logs.data);
</script>

<template>

    <Head title="Log sistema" />
    <AuthenticatedLayout section="logs">
        <h2 class="mb-4">Audit Logs</h2>

        <div class="mb-3 d-flex">
            <input v-model="search" type="text" class="form-control me-2" placeholder="Cerca per azione o modello" />
            <Link :href="`/admin/audit-logs?search=${search}`" class="main-button">
                Cerca
            </Link>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Utente</th>
                    <th>Azione</th>
                    <th>Modello</th>
                    <th>ID Modello</th>
                    <th>Vecchi valori</th>
                    <th>Nuovi valori</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in filteredLogs" :key="log.id">
                    <td>{{ new Date(log.created_at).toLocaleString() }}</td>
                    <td>{{ `${log.user?.name} ${log.user?.surname}` || 'Sconosciuto' }}</td>
                    <td>
                        <span class="badge" :class="{
                            'bg-primary': log.event === 'viewed' || log.event === 'viewed all patients' || log.event === 'viewed all consent types' || log.event === 'viewed all consent versions' || log.event === 'viewed all patient consents' || log.event === 'viewed consent document',
                            'bg-success': log.event === 'login',
                            'bg-secondary': log.event === 'logout',
                            'bg-danger': log.event === 'login_failed',
                            'bg-warning': ['created', 'updated', 'deleted', 'update', 'status changed'].includes(log.event),
                            'bg-dark': log.event === 'show_pdf' || log.event === 'void'
                        }">
                            {{ log.event }}
                        </span>
                    </td>
                    <td>{{ log.auditable_type }}</td>
                    <td>{{ log.auditable_id }}</td>
                    <td>
                        <pre>{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                    </td>
                    <td>
                        <pre>{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mt-3">
            <Link v-if="props.logs.prev_page_url" :href="props.logs.prev_page_url" class="btn btn-secondary me-2">Prec
            </Link>
            <Link v-if="props.logs.next_page_url" :href="props.logs.next_page_url" class="btn btn-secondary">Succ</Link>
        </div>
    </AuthenticatedLayout>
</template>
