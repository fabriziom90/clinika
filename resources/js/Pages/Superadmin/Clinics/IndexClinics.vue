<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { reactive } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    clinics: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? "",
    status: props.filters?.status ?? "",
});

const applyFilters = () => {
    router.get(route("superadmin.clinics.index"), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

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
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cerca</label>

                        <input v-model="filters.search" type="text" class="form-control" placeholder="Nome o slug..."
                            @keyup.enter="applyFilters">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stato</label>

                        <select v-model="filters.status" class="form-select">
                            <option value="">Tutte</option>
                            <option value="active">Attive</option>
                            <option value="inactive">Inattive</option>
                            <option value="deleted">Disattivate</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="main-button w-100" @click="applyFilters">
                            Filtra
                        </button>
                    </div>
                </div>
            </div>
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
                    <div v-if="clinics.links?.length > 3" class="d-flex justify-content-center mt-4">
                        <div class="d-flex gap-1">
                            <template v-for="link in clinics.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" class="btn btn-sm"
                                    :class="{ 'btn-primary': link.active, 'btn-light': !link.active }"
                                    v-html="link.label" preserve-scroll preserve-state />

                                <span v-else class="btn btn-sm btn-light disabled" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>