<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import Table from "@/Components/Table.vue";
import { ref } from "vue";

const props = defineProps({
    products: Array,
    columns: Object,
});

const showAddForm = ref(false);
const localProduct = ref([...props.products]);
const form = useForm({
    name: "",
    unit_price: "",
});

const handleSubmitForm = () => {
    form.post(route("admin.products.store"), {
        onSuccess: (page) => {
            // aggiorna la tabella con i nuovi dati passati dal controller
            localProduct.value = page.props.products;

            form.reset();
            showAddForm.value = false;
        },
        onError: (errors) => {
            $toast.error("Errore durante il salvataggio", {
                position: "top-right",
                duration: 3000,
            });
        },
    });
};

const handleInlineUpdate = (updatedData) => {
    localProduct.value = updatedData;
};
</script>
<template lang="">
    <Head title="Prodotti medici" />
    <AuthenticatedLayout section="products">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco prodotti medici</h2>
                <button class="main-button" @click="showAddForm = !showAddForm">
                    Aggiungi prodotto
                </button>
            </div>
        </div>
        <div v-if="showAddForm" class="bg-main-red mb-3 text-white p-3">
            <form
                @submit.prevent="handleSubmitForm"
                class="d-flex align-items-end"
            >
                <div class="me-3 w-50">
                    <label class="form-label">Nome prodotto</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Inserisci il nome del prodotto"
                        v-model="form.name"
                    />
                </div>
                <div class="me-3 w-50">
                    <label class="form-label">Prezzo unitario</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        placeholder="Inserisci il prezzo unitario del prodotto"
                        v-model="form.unit_price"
                    />
                </div>
                <div>
                    <button type="submit" class="btn-negative">Salva</button>
                </div>
            </form>
        </div>
        <Table
            :items="products"
            :columns="columns"
            baseRoute="admin.products"
            :editableColumns="['name', 'unit_price']"
            @updated="handleInlineUpdate"
        />
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
