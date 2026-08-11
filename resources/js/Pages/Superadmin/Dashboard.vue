<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            clinics: 0,
            activeClinics: 0,
            inactiveClinics: 0,
            admins: 0,
            users: 0,
        }),
    },

    clinics: {
        type: Array,
        default: () => [],
    },

    recentActivities: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>

    <Head title="Dashboard Superadmin" />

    <AuthenticatedLayout section="superadmin">
        <div class="row align-items-center">
            <div class="col">
                <h2>Dashboard Superadmin</h2>

                <p class="text-muted mb-0">
                    Panoramica della piattaforma Clinika.
                </p>
            </div>

            <div class="col-auto">
                <Link :href="route('superadmin.clinics.index')" class="main-button">
                    <i class="fa-solid fa-hospital me-2"></i>
                    Gestisci cliniche
                </Link>
            </div>
        </div>

        <!-- STATISTICHE -->
        <div class="row g-4 mt-2">
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-3 text-primary">
                                <i class="fa-solid fa-hospital"></i>
                            </div>

                            <div>
                                <h6 class="text-muted mb-1">
                                    Cliniche totali
                                </h6>

                                <h3 class="mb-0">
                                    {{ stats.clinics }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-3 text-success">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>

                            <div>
                                <h6 class="text-muted mb-1">
                                    Cliniche attive
                                </h6>

                                <h3 class="mb-0">
                                    {{ stats.activeClinics }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-3 text-danger">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>

                            <div>
                                <h6 class="text-muted mb-1">
                                    Cliniche disattivate
                                </h6>

                                <h3 class="mb-0">
                                    {{ stats.inactiveClinics }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-3 text-info">
                                <i class="fa-solid fa-users"></i>
                            </div>

                            <div>
                                <h6 class="text-muted mb-1">
                                    Utenti totali
                                </h6>

                                <h3 class="mb-0">
                                    {{ stats.users }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CLINICHE -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">
                            Cliniche
                        </h4>

                        <p class="text-muted mb-0">
                            Stato e dimensione dei tenant presenti sulla
                            piattaforma.
                        </p>
                    </div>

                    <Link :href="route('superadmin.clinics.index')" class="btn btn-sm btn-outline-primary">
                        Vedi tutte
                    </Link>
                </div>

                <div v-if="clinics.length" class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Clinica</th>
                                <th>Stato</th>
                                <th class="text-center">
                                    <i class="fa-solid fa-user-shield" title="Admin"></i>
                                </th>
                                <th class="text-center">
                                    <i class="fa-solid fa-user-doctor" title="Medici"></i>
                                </th>
                                <th class="text-center">
                                    <i class="fa-solid fa-user-nurse" title="Infermieri"></i>
                                </th>
                                <th class="text-center">
                                    <i class="fa-solid fa-user-tie" title="Segretarie"></i>
                                </th>
                                <th class="text-end">
                                    Azioni
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="clinic in clinics" :key="clinic.id">
                                <td>
                                    <strong>
                                        {{ clinic.name }}
                                    </strong>

                                    <div class="small text-muted">
                                        {{ clinic.slug }}
                                    </div>
                                </td>

                                <td>
                                    <span v-if="clinic.active" class="badge bg-success">
                                        Attiva
                                    </span>

                                    <span v-else class="badge bg-secondary">
                                        Disattivata
                                    </span>
                                </td>

                                <td class="text-center">
                                    {{ clinic.admins }}
                                </td>

                                <td class="text-center">
                                    {{ clinic.doctors }}
                                </td>

                                <td class="text-center">
                                    {{ clinic.nurses }}
                                </td>

                                <td class="text-center">
                                    {{ clinic.secretaries }}
                                </td>

                                <td class="text-end">
                                    <Link :href="route(
                                        'superadmin.clinics.show',
                                        clinic.id
                                    )" class="btn btn-sm btn-primary" title="Dettaglio clinica">
                                        <i class="fa-solid fa-eye"></i>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-5">
                    <i class="fa-solid fa-hospital fs-1 text-muted mb-3"></i>

                    <p class="text-muted mb-3">
                        Non è ancora presente alcuna clinica.
                    </p>

                    <Link :href="route('superadmin.clinics.create')" class="main-button">
                        <i class="fa-solid fa-plus me-2"></i>
                        Crea clinica
                    </Link>
                </div>
            </div>
        </div>

        <!-- ATTIVITÀ RECENTE -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="mb-4">
                    <h4 class="mb-1">
                        Attività recente
                    </h4>

                    <p class="text-muted mb-0">
                        Ultime attività rilevate sulla piattaforma.
                    </p>
                </div>

                <div v-if="recentActivities.length" class="list-group list-group-flush">
                    <div v-for="activity in recentActivities" :key="activity.id" class="list-group-item px-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="activity-icon" :class="`activity-${activity.type}`">
                                    <i :class="activity.icon ||
                                        'fa-solid fa-circle-info'
                                        "></i>
                                </div>

                                <div>
                                    <div>
                                        <strong>{{ activity.title }}</strong>
                                    </div>

                                    <div v-if="activity.subject" class="small">
                                        {{ activity.subject }}
                                    </div>

                                    <div class="small text-muted">
                                        da {{ activity.user }}
                                    </div>
                                </div>
                            </div>

                            <div class="small text-muted">
                                {{ activity.created_at }}
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-4">
                    <i class="fa-solid fa-clock-rotate-left fs-3 text-muted mb-2"></i>

                    <p class="text-muted mb-0">
                        Nessuna attività recente.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style lang="scss" scoped>
.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-created {
    background: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.activity-updated {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.activity-deleted {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.activity-login {
    background: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.activity-warning {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}
</style>
