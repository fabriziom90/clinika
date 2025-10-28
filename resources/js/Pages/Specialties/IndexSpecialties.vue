<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";

const props = defineProps({
    specialties: Array,
    columns: Object,
});

const showAddForm = ref(false);
const localSpecialties = ref([...props.specialties]);

const form = useForm({
    name: "",
});

const handleSubmitForm = () => {
    form.post(route("admin.specialties.store"), {
        onSuccess: (page) => {
            // aggiorna la tabella con i nuovi dati passati dal controller
            localSpecialties.value = page.props.specialties;

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
    console.log(updatedData);
    localSpecialties.value = updatedData;
};
</script>

<template>
    <Head title="Specializzazioni" />
    <AuthenticatedLayout section="specialties">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco specializzazioni</h2>
                <button class="main-button" @click="showAddForm = !showAddForm">
                    Aggiungi specializzazione
                </button>
            </div>

            <div v-if="showAddForm" class="bg-main-red mb-3 text-white p-3">
                <form
                    @submit.prevent="handleSubmitForm"
                    class="d-flex align-items-end"
                >
                    <div class="me-3 w-100">
                        <label class="form-label">Nome specializzazione</label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Inserisci nome specializzazione"
                            v-model="form.name"
                        />
                    </div>
                    <div>
                        <button type="submit" class="btn-negative">
                            Salva
                        </button>
                    </div>
                </form>
            </div>

            <Table
                :items="localSpecialties"
                :columns="columns"
                baseRoute="admin.specialties"
                :editableColumns="['name']"
                @updated="handleInlineUpdate"
            />
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped>
@use "../../../scss/app.scss";
</style>
