<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChangeInvoiceStatusModal from './ChangeInvoiceStatusModal.vue';
import DeleteInvoiceModal from './DeleteInvoiceModal.vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Object,
    filters: Object,
    statistics: Object
});

const showStatusModal = ref(false);
const showDeleteModal = ref(false);
const selectedInvoice = ref(null);

// filters' state
const searchFilters = ref({
    number: props.filters?.number ?? '',
    year: props.filters?.year ?? '',
    patient: props.filters?.patient ?? '',
    doctor: props.filters?.doctor ?? '',
    status: props.filters?.status ?? ''
});

// Variable per salvare il timer del debounce fatto a mano
let searchTimer = null;

watch(searchFilters, (newValues) => {
    // 1. delete previous timer everytime user press a button
    clearTimeout(searchTimer);

    // 2. starts new timer
    searchTimer = setTimeout(() => {
        const cleanFilters = Object.fromEntries(
            Object.entries(newValues).filter(([_, v]) => v !== '')
        );

        router.get(route('admin.invoices.index'), cleanFilters, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 400);
}, { deep: true });


const statusLabel = (status) => {
    const labels = {
        draft: "Bozza",
        issued: "Emessa",
        paid: "Pagata",
        cancelled: "Annullata"
    };
    return labels[status] ?? status;
};

const statusClass = (status) => {
    const classes = {
        draft: "badge bg-secondary",
        issued: "badge bg-primary",
        paid: "badge bg-success",
        cancelled: "badge bg-danger"
    };
    return classes[status] ?? "badge-secondary";
};

const formatDate = (date) => {
    if (!date) return '';
    const dateObj = new Date(date);
    return dateObj.toLocaleDateString('it-IT');
};

const changePage = (page) => {
    router.get(route('admin.invoices.index'), { ...searchFilters.value, page: page }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openStatusModal = (invoice) => {
    selectedInvoice.value = invoice;
    showStatusModal.value = true;
};

const openDeleteModal = (invoice) => {
    selectedInvoice.value = invoice;
    showDeleteModal.value = true;
};

const closeModal = () => {
    selectedInvoice.value = null;
    showStatusModal.value = false;
    showDeleteModal.value = false;
};

const handleStatusUpdated = (payload) => {
    // reload server side datas to show statistics
    router.reload({ only: ['invoices', 'statistics'] });
};

const exportData = () => {
    // Rimuove filtri vuoti
    const cleanFilters = Object.fromEntries(
        Object.entries(searchFilters.value).filter(([_, v]) => v !== '')
    );

    // Genera l'URL completo con i query parameters e avvia il download
    const url = route('admin.invoices.export', cleanFilters);
    window.location.href = url;
};
</script>

<template>

    <Head title="Elenco fatture" />
    <AuthenticatedLayout section="invoices">
        <div class="container-fluid">

            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <h2>Fatture</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="main-button" @click="exportData">
                        <i class="fas fa-file-export me-2"></i> Esporta Report
                    </button>
                </div>
            </div>

            <!-- Dashboard Statistiche -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-success text-white h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase mb-1"><i class="fas fa-check-circle me-1"></i>
                                Incassato (Pagate)</h6>
                            <h3 class="mb-0">{{ Number(statistics.by_status.paid.amount).toFixed(2) }} €</h3>
                            <small>{{ statistics.by_status.paid.count }} fatture</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-primary text-white h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase mb-1"><i class="fas fa-paper-plane me-1"></i> Da
                                Incassare (Emesse)</h6>
                            <h3 class="mb-0">{{ Number(statistics.by_status.issued.amount).toFixed(2) }} €</h3>
                            <small>{{ statistics.by_status.issued.count }} fatture</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-secondary text-white h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase mb-1"><i class="fas fa-file-alt me-1"></i> In Bozza
                            </h6>
                            <h3 class="mb-0">{{ Number(statistics.by_status.draft.amount).toFixed(2) }} €</h3>
                            <small>{{ statistics.by_status.draft.count }} fatture</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-dark text-white h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase mb-1"><i class="fas fa-calculator me-1"></i> Totale
                                Globale</h6>
                            <h3 class="mb-0">{{ Number(statistics.grand_total).toFixed(2) }} €</h3>
                            <small>{{ statistics.grand_count }} fatture totali visualizzate</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle custom-table mb-0">
                            <thead>
                                <!-- Intestazioni -->
                                <tr>
                                    <th>Numero</th>
                                    <th>Anno (Data)</th>
                                    <th>Paziente</th>
                                    <th>Medico</th>
                                    <th>Totale</th>
                                    <th>Stato</th>
                                    <th>Azioni</th>
                                </tr>

                                <!-- Riga filtri -->
                                <tr>
                                    <th>
                                        <input v-model="searchFilters.number" type="text"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Filtra n..." />
                                    </th>
                                    <th>
                                        <input v-model="searchFilters.year" type="number"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Es. 2026..." />
                                    </th>
                                    <th>
                                        <input v-model="searchFilters.patient" type="text"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Nome paziente..." />
                                    </th>
                                    <th>
                                        <input v-model="searchFilters.doctor" type="text"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Nome medico..." />
                                    </th>
                                    <th></th>
                                    <th>
                                        <select v-model="searchFilters.status"
                                            class="form-select form-select-sm column-filter">
                                            <option value="">Tutti</option>
                                            <option value="paid">Pagata</option>
                                            <option value="issued">Emessa</option>
                                            <option value="draft">Bozza</option>
                                            <option value="cancelled">Annullata</option>
                                        </select>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in invoices.data" :key="invoice.id">
                                    <td class="fw-bold">{{ invoice.number }}</td>
                                    <td>{{ formatDate(invoice.date) }}</td>
                                    <td>{{ invoice.full_name || (invoice.patient ? `${invoice.patient.name}
                                        ${invoice.patient.surname}` : '-') }}</td>
                                    <td>{{ invoice.doctor?.user ? `${invoice.doctor.user.name}
                                        ${invoice.doctor.user.surname}` : '-' }}</td>
                                    <td class="fw-semibold">{{ Number(invoice.amount).toFixed(2) }} €</td>
                                    <td>
                                        <span :class="statusClass(invoice.status)" style="cursor:pointer"
                                            @click="openStatusModal(invoice)">
                                            {{ statusLabel(invoice.status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a v-if="invoice.status !== 'draft' && invoice.status !== 'cancelled'"
                                                :href="route('admin.invoices.show', invoice.uuid)" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <Link v-if="invoice.status !== 'paid'"
                                                :href="route('admin.invoices.edit', invoice.uuid)"
                                                class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </Link>
                                            <button v-if="invoice.status !== 'paid'" class="btn btn-sm btn-danger"
                                                @click="openDeleteModal(invoice)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!invoices.data.length">
                                    <td colspan="7" class="text-center py-4">Nessuna fattura trovata</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginazione -->
                    <div class="pagination d-flex justify-content-between align-items-center mt-3 mb-5">
                        <button class="btn btn-outline-secondary btn-sm" :disabled="invoices.current_page === 1"
                            @click="changePage(invoices.current_page - 1)">
                            <i class="fas fa-angle-left"></i> Precedente
                        </button>
                        <span>Pagina <strong>{{ invoices.current_page }}</strong> di <strong>{{ invoices.last_page
                                }}</strong></span>
                        <button class="btn btn-outline-secondary btn-sm"
                            :disabled="invoices.current_page === invoices.last_page"
                            @click="changePage(invoices.current_page + 1)">
                            Successiva <i class="fas fa-angle-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <ChangeInvoiceStatusModal :show="showStatusModal" :invoice="selectedInvoice" @close="closeModal"
                @updated="handleStatusUpdated" />
            <DeleteInvoiceModal :show="showDeleteModal" :invoice="selectedInvoice" @close="closeModal" />
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped>
.column-filter {
    font-size: 0.85rem;
}

.card {
    transition: transform 0.2s;

    &:hover {
        transform: translateY(-3px);
    }
}
</style>