<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    clinics: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    clinic_id: "",
    name: "",
    surname: "",
    email: "",
});

const submit = () => {
    form.post(route("superadmin.admins.store"));
};
</script>

<template>

    <Head title="Nuovo admin" />

    <AuthenticatedLayout section="superadmin">
        <div class="row">
            <div class="col-12">
                <h2>Nuovo admin</h2>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="mb-4">Informazioni admin</h4>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Clinica *</label>

                            <select v-model="form.clinic_id" class="form-select">
                                <option value="">
                                    Seleziona una clinica
                                </option>

                                <option v-for="clinic in props.clinics" :key="clinic.id" :value="clinic.id">
                                    {{ clinic.name }}
                                </option>
                            </select>

                            <div v-if="form.errors.clinic_id" class="text-danger">
                                {{ form.errors.clinic_id }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nome *</label>

                            <input v-model="form.name" type="text" class="form-control">

                            <div v-if="form.errors.name" class="text-danger">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cognome *</label>

                            <input v-model="form.surname" type="text" class="form-control">

                            <div v-if="form.errors.surname" class="text-danger">
                                {{ form.errors.surname }}
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email *</label>

                            <input v-model="form.email" type="email" class="form-control">

                            <div v-if="form.errors.email" class="text-danger">
                                {{ form.errors.email }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <Link :href="route('superadmin.admins.index')" class="btn btn-secondary">
                    Annulla
                </Link>

                <button type="submit" class="main-button" :disabled="form.processing">
                    Salva admin
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>