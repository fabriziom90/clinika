<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    clinic: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.clinic.name ?? "",
    slug: props.clinic.slug ?? "",
    email: props.clinic.email ?? "",
    phone: props.clinic.phone ?? "",
    address: props.clinic.address ?? "",
    city: props.clinic.city ?? "",
    province: props.clinic.province ?? "",
    zip_code: props.clinic.zip_code ?? "",
    vat_number: props.clinic.vat_number ?? "",
    tax_code: props.clinic.tax_code ?? "",
    logo: props.clinic.logo ?? "",
    database: props.clinic.database ?? "",
    db_host: props.clinic.db_host ?? "",
    db_port: props.clinic.db_port ?? "3306",
    db_username: props.clinic.db_username ?? "",
    db_password: "",
    active: props.clinic.active ?? true,
});

const submit = () => {
    form.put(route("superadmin.clinics.update", props.clinic.id));
};
</script>

<template>

    <Head :title="`Modifica ${clinic.name}`" />

    <AuthenticatedLayout section="superadmin">
        <div class="row">
            <div class="col-12">
                <h2>Modifica clinica</h2>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="mb-4">Informazioni clinica</h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome *</label>
                            <input v-model="form.name" type="text" class="form-control">
                            <div v-if="form.errors.name" class="text-danger">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Slug *</label>
                            <input v-model="form.slug" type="text" class="form-control">
                            <div v-if="form.errors.slug" class="text-danger">
                                {{ form.errors.slug }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input v-model="form.email" type="email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input v-model="form.phone" type="text" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Indirizzo</label>
                            <input v-model="form.address" type="text" class="form-control">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Città</label>
                            <input v-model="form.city" type="text" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Provincia</label>
                            <input v-model="form.province" type="text" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">CAP</label>
                            <input v-model="form.zip_code" type="text" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Partita IVA</label>
                            <input v-model="form.vat_number" type="text" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Codice fiscale</label>
                            <input v-model="form.tax_code" type="text" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Logo</label>
                            <input v-model="form.logo" type="text" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="mb-4">Database tenant</h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Database *</label>
                            <input v-model="form.database" type="text" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Host *</label>
                            <input v-model="form.db_host" type="text" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Port *</label>
                            <input v-model="form.db_port" type="text" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Username *</label>
                            <input v-model="form.db_username" type="text" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Nuova password
                            </label>

                            <input v-model="form.db_password" type="password" class="form-control">

                            <small class="text-muted">
                                Lascia vuoto per mantenere quella attuale.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="form-check">
                        <input id="active" v-model="form.active" type="checkbox" class="form-check-input">

                        <label for="active" class="form-check-label">
                            Clinica attiva
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <Link :href="route('superadmin.clinics.index')" class="btn btn-secondary">
                    Annulla
                </Link>

                <button type="submit" class="main-button" :disabled="form.processing">
                    Salva modifiche
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>