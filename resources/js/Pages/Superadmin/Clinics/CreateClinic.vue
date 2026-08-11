<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const form = useForm({
    name: "",
    email: "",
    phone: "",
    address: "",
    city: "",
    province: "",
    zip_code: "",
    vat_number: "",
    tax_code: "",
    logo_path: null,
    database: "",
    db_host: "127.0.0.1",
    db_port: "3306",
    db_username: "",
    db_password: "",
    active: true,
});

const submit = () => {
    form.post(route("superadmin.clinics.store"), {
        forceFormData: true,
    });
};
</script>

<template>

    <Head title="Nuova clinica" />

    <AuthenticatedLayout section="superadmin">
        <div class="row">
            <div class="col-12">
                <h2>Nuova clinica</h2>
            </div>
        </div>
        <div v-if="Object.keys(form.errors).length" class="alert alert-danger">
            <ul class="mb-0">
                <li v-for="(error, field) in form.errors" :key="field">
                    {{ error }}
                </li>
            </ul>
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
                            <label class="form-label">Email</label>
                            <input v-model="form.email" type="email" class="form-control">
                            <div v-if="form.errors.email" class="text-danger">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input v-model="form.phone" type="text" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp"
                                @change="form.logo_path = $event.target.files[0]">
                            <div v-if="form.errors.logo_path" class="text-danger">
                                {{ form.errors.logo_path }}
                            </div>
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
                            <div v-if="form.errors.database" class="text-danger">
                                {{ form.errors.database }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Host *</label>
                            <input v-model="form.db_host" type="text" class="form-control">
                            <div v-if="form.errors.db_host" class="text-danger">
                                {{ form.errors.db_host }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Port *</label>
                            <input v-model="form.db_port" type="text" class="form-control">
                            <div v-if="form.errors.db_port" class="text-danger">
                                {{ form.errors.db_port }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Username *</label>
                            <input v-model="form.db_username" type="text" class="form-control">
                            <div v-if="form.errors.db_username" class="text-danger">
                                {{ form.errors.db_username }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Password</label>
                            <input v-model="form.db_password" type="password" class="form-control">
                            <div v-if="form.errors.db_password" class="text-danger">
                                {{ form.errors.db_password }}
                            </div>
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
                    Salva clinica
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
