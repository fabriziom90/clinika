<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChangeInvoiceStatusModal from './ChangeInvoiceStatusModal.vue';
import DeleteInvoiceModal from './DeleteInvoiceModal.vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Object
})


const showStatusModal = ref(false);
const showDeleteModal = ref(false);
const selectedInvoice = ref(null);


const statusLabel = (status) => {
    const labels = {
        draft: "Bozza",
        issued: "Emessa",
        paid: "Pagata",
        cancelled: "Annullata"
    }


    return labels[status] ?? status;
}

const statusClass = (status) => {
    const classes = {
        draft: "badge bg-secondary",
        issued: "badge bg-primary",
        paid: "badge bg-success",
        cancelled: "badge bg-danger"
    }

    return classes[status] ?? "badge-secondary";
}

const formatDate = (date) => {
    const dateObj = new Date(date);
    const year = dateObj.getFullYear();
    const month = dateObj.getMonth() + 1;
    const day = dateObj.getDate();

    return `${day}/${month}/${year}`;
};

const changePage = (page) => {
    router.get(
        route('admin.invoices.index'),
        {
            page: page
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const openStatusModal = (invoice) => {
    selectedInvoice.value = invoice;
    showStatusModal.value = true;
}

const openDeleteModal = (invoice) => {
    selectedInvoice.value = invoice;
    showDeleteModal.value = true;
}

const closeModal = () => {

    selectedInvoice.value = null;
    showStatusModal.value = false;
    showDeleteModal.value = false;
}

const handleStatusUpdated = (payload) => {

    const invoice = props.invoices.data.find(invoice => invoice.id === payload.id);

    if (invoice) invoice.status = payload.status;
}

const handleDelete = () => {

}

</script>
<template>

    <Head title="Elenco fatture" />
    <AuthenticatedLayout section="invoices">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2>Fatture</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Numero</th>
                                <th>Data</th>
                                <th>Paziente</th>
                                <th>Prestazione</th>
                                <th>Totale</th>
                                <th>Stato</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="invoice in invoices.data" :key="invoice.id">
                                <td>
                                    {{ invoice.number }}
                                </td>
                                <td>
                                    {{ formatDate(invoice.date) }}
                                </td>
                                <td>
                                    {{ invoice.full_name }}
                                </td>
                                <td>
                                    <div v-for="item in invoice.invoice_items" :key="item.id">
                                        {{ item.description }}
                                    </div>
                                </td>
                                <td>
                                    {{ Number(invoice.amount).toFixed(2) }} €
                                </td>
                                <td>
                                    <span :class="statusClass(invoice.status)" style="cursor:pointer"
                                        @click="openStatusModal(invoice)">
                                        {{ statusLabel(invoice.status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a v-if="invoice.status != 'draft' && invoice.status != 'cancelled'"
                                            :href="route('admin.invoices.show', invoice.uuid)" target="_blank"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <Link v-if="invoice.status != 'paid'"
                                            :href="route('admin.invoices.edit', invoice.uuid)"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button v-if="invoice.status != 'paid'" class="btn btn-sm btn-danger"
                                            @click="openDeleteModal(invoice)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!invoices.data.length">
                                <td colspan="7" class="text-center">
                                    Nessuna fattura presente
                                </td>
                            </tr>
                        </tbody>
                    </table>


                    <div class="pagination">
                        <button :disabled="invoices.current_page === 1" @click="changePage(invoices.current_page - 1)">
                            <i class="fas fa-angle-left"></i>
                        </button>
                        <span>
                            Pagina {{ invoices.current_page }} di {{ invoices.last_page }}
                        </span>
                        <button :disabled="invoices.current_page === invoices.last_page"
                            @click="changePage(invoices.current_page + 1)">
                            <i class="fas fa-angle-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <ChangeInvoiceStatusModal :show="showStatusModal" :invoice="selectedInvoice" @close="closeModal"
                @updated="handleStatusUpdated" />
            <DeleteInvoiceModal :show="showDeleteModal" :invoice="selectedInvoice" @close="closeModal"
                @deleted="handleDelete" />
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss"></style>
