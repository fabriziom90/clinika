<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

defineProps({
    clinic: {
        type: Object,
        required: true,
    },
});
</script>

<template>

    <Head :title="clinic.name" />

    <AuthenticatedLayout section="superadmin">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>{{ clinic.name }}</h2>

            <div class="d-flex gap-2">
                <Link :href="route('superadmin.clinics.edit', clinic.id)" class="edit-button">
                    <i class="fas fa-edit"></i>
                    Modifica
                </Link>

                <Link :href="route('superadmin.clinics.index')" class="btn btn-secondary">
                    Indietro
                </Link>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-4">Informazioni clinica</h4>

                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Nome</strong>
                        <div>{{ clinic.name }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Slug</strong>
                        <div>{{ clinic.slug }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Email</strong>
                        <div>{{ clinic.email || "-" }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Telefono</strong>
                        <div>{{ clinic.phone || "-" }}</div>
                    </div>

                    <div class="col-12">
                        <strong>Indirizzo</strong>
                        <div>{{ clinic.address || "-" }}</div>
                    </div>

                    <div class="col-md-5">
                        <strong>Città</strong>
                        <div>{{ clinic.city || "-" }}</div>
                    </div>

                    <div class="col-md-3">
                        <strong>Provincia</strong>
                        <div>{{ clinic.province || "-" }}</div>
                    </div>

                    <div class="col-md-4">
                        <strong>CAP</strong>
                        <div>{{ clinic.zip_code || "-" }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Partita IVA</strong>
                        <div>{{ clinic.vat_number || "-" }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Codice fiscale</strong>
                        <div>{{ clinic.tax_code || "-" }}</div>
                    </div>

                    <div class="col-12">
                        <strong>Logo</strong>
                        <div>{{ clinic.logo || "-" }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-4">Database tenant</h4>

                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Database</strong>
                        <div>{{ clinic.database }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>Host</strong>
                        <div>{{ clinic.db_host }}</div>
                    </div>

                    <div class="col-md-4">
                        <strong>Port</strong>
                        <div>{{ clinic.db_port }}</div>
                    </div>

                    <div class="col-md-4">
                        <strong>Username</strong>
                        <div>{{ clinic.db_username }}</div>
                    </div>

                    <div class="col-md-4">
                        <strong>Password</strong>
                        <div>••••••••</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">Stato</h4>

                <span v-if="clinic.deleted_at" class="badge bg-danger">
                    Disattivata
                </span>

                <span v-else-if="clinic.active" class="badge bg-success">
                    Attiva
                </span>

                <span v-else class="badge bg-secondary">
                    Inattiva
                </span>
            </div>
        </div>
    </AuthenticatedLayout>
</template>