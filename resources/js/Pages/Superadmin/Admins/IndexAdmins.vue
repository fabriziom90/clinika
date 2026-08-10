<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    admins: {
        type: Array,
        default: () => [],
    },
});

const deleteAdmin = (admin) => {
    if (!confirm(`Sei sicuro di voler eliminare l'admin ${admin.name} ${admin.surname}?`)) {
        return;
    }

    router.delete(route("superadmin.admins.destroy", admin.id));
};

const resendEmail = (admin) => {
    if (!confirm(`Vuoi rinviare l'email per l'impostazione della password a ${admin.email}?`)) {
        return;
    }

    router.post(route("superadmin.admins.resend-email", admin.id));
};
</script>

<template>

    <Head title="Admin cliniche" />

    <AuthenticatedLayout section="superadmin">

        <div class="row align-items-center">
            <div class="col">
                <h2>Admin cliniche</h2>
            </div>

            <div class="col-auto">
                <Link :href="route('superadmin.admins.create')" class="main-button">
                    Nuovo admin
                </Link>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">

                <div v-if="admins.length" class="table-responsive">

                    <table class="table align-middle">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Clinica</th>
                                <th>Nome</th>
                                <th>Cognome</th>
                                <th>Email</th>
                                <th>Inserito il</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr v-for="admin in admins" :key="`${admin.clinic_id}-${admin.id}`">

                                <td>
                                    {{ admin.id }}
                                </td>

                                <td>
                                    <strong>{{ admin.clinic_name }}</strong>
                                </td>

                                <td>
                                    {{ admin.name }}
                                </td>

                                <td>
                                    {{ admin.surname }}
                                </td>

                                <td>
                                    {{ admin.email }}
                                </td>

                                <td>
                                    {{ admin.created_at }}
                                </td>

                                <td class="text-end">

                                    <div class="d-flex justify-content-end gap-2">

                                        <Link :href="route('superadmin.admins.show', {
                                            clinic: admin.clinic_id,
                                            admin: admin.id,
                                        })" class="btn btn-sm btn-info" title="Dettagli">
                                            <i class="fa-solid fa-eye"></i>
                                        </Link>

                                        <Link :href="route('superadmin.admins.edit', {
                                            clinic: admin.clinic_id,
                                            admin: admin.id,
                                        })" class="btn btn-sm btn-primary" title="Modifica">
                                            <i class="fa-solid fa-pen"></i>
                                        </Link>

                                        <button type="button" class="btn btn-sm btn-warning" title="Reinvia email"
                                            @click="resendEmail(admin)">
                                            <i class="fa-solid fa-envelope"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-danger" title="Elimina"
                                            @click="deleteAdmin(admin)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <div v-else class="text-center py-5">

                    <p class="text-muted mb-3">
                        Non è stato ancora creato alcun admin.
                    </p>

                    <Link :href="route('superadmin.admins.create')" class="main-button">
                        Crea il primo admin
                    </Link>

                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
