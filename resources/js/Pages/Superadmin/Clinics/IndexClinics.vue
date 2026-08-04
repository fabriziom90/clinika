<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    clinics: Object,
});

const deleteClinic = (clinic) => {
    if (!confirm(`Disattivare la clinica "${clinic.name}"?`)) {
        return;
    }

    router.delete(route("superadmin.clinics.destroy", clinic.id), {
        preserveScroll: true,
    });
};

const restoreClinic = (clinic) => {
    router.patch(route("superadmin.clinics.restore", clinic.id), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Cliniche" />

    <AuthenticatedLayout section="superadmin">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Cliniche</h2>

            <Link :href="route('superadmin.clinics.create')" class="main-button">
                Nuova clinica
            </Link>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Slug</th>
                                <th>Database</th>
                                <th>Stato</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="clinic in clinics.data" :key="clinic.id">
                                <td>{{ clinic.name }}</td>

                                <td>{{ clinic.slug }}</td>

                                <td>{{ clinic.database }}</td>

                                <td>
                                    <span v-if="clinic.deleted_at" class="badge bg-danger">
                                        Disattiva
                                    </span>

                                    <span v-else-if="clinic.active" class="badge bg-success">
                                        Attiva
                                    </span>

                                    <span v-else class="badge bg-secondary">
                                        Inattiva
                                    </span>
                                </td>

                                <td class="text-end">
                                    <Link :href="route(
                                        'superadmin.clinics.show',
                                        clinic.id
                                    )
                                        " class="btn-blue me-1">
                                        <i class="fas fa-eye"></i>
                                    </Link>

                                    <Link v-if="!clinic.deleted_at" :href="route(
                                        'superadmin.clinics.edit',
                                        clinic.id
                                    )
                                        " class="edit-button me-1">
                                        <i class="fas fa-edit"></i>
                                    </Link>

                                    <button v-if="!clinic.deleted_at" type="button" class="delete-button"
                                        @click="deleteClinic(clinic)">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <button v-else type="button" class="btn btn-success" @click="restoreClinic(clinic)">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!clinics.data.length">
                                <td colspan="5" class="text-center">
                                    Nessuna clinica presente.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>