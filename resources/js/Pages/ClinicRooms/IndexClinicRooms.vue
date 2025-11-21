<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    clinicRooms: Array,
    columns: Object,
});

const showAddForm = ref(false);
const localClinicRoom = ref([...props.clinicRooms]);
const form = useForm({
    name: "",
});

const handleSubmitForm = () => {
    form.post(route("admin.clinic-rooms.store"), {
        onSuccess: (page) => {
            // aggiorna la tabella con i nuovi dati passati dal controller
            localClinicRoom.value = page.props.clinicRooms;

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
    localClinicRoom.value = updatedData;
};
</script>
<template lang="">
    <Head title="Stanze Poliambulatorio" />
    <AuthenticatedLayout section="clinicrooms">
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Elenco stanze</h2>
                <button class="main-button" @click="showAddForm = !showAddForm">
                    Aggiungi stanza
                </button>
            </div>
        </div>
        <div v-if="showAddForm" class="bg-main-red mb-3 text-white p-3">
            <form
                @submit.prevent="handleSubmitForm"
                class="d-flex align-items-end"
            >
                <div class="me-3 w-100">
                    <label class="form-label">Nome stanza</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Inserisci il nome della stanza"
                        v-model="form.name"
                    />
                </div>
                <div>
                    <button type="submit" class="btn-negative">Salva</button>
                </div>
            </form>
        </div>
        <Table
            :items="clinicRooms"
            :columns="columns"
            baseRoute="admin.clinic-rooms"
            :editableColumns="['name']"
            @updated="handleInlineUpdate"
        />
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
