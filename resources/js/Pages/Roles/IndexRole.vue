<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";

const props = defineProps({
    roles: Array,
    permissions: Array,
});

const groupedPermissions = props.permissions.reduce((groups, permission) => {
    const group = permission.name.split('.')[0];

    if (!groups[group]) {
        groups[group] = [];
    }

    groups[group].push(permission);

    return groups;
}, {});

const groupMap = {
    'patient': 'Pazienti',
    'doctor': 'Dottori',
    'nurse': 'Infermieri',
    'user': 'Utenti',
    'appointment': 'Appuntamenti',
    'role': 'Ruoli',
    'specialty': 'Specializzazioni',
    'clinic-room': 'Ambulatori',
    'product': 'Prodotti medici',
    'drug': 'Medicinali',
    'service': 'Prestazione sanitaria',
    'medical-record': 'Cartella clinica',
    'medical-entry': 'Voce cartella clinica',
    'medical-attachment': 'Allegato clinico',
    'prescription': 'Prescrizione',
    'vital-parameter': 'Parametri vitali',
    'invoices': 'Fatture'
}


const togglePermission = (roleId, permissionId) => {
    router.post(route("admin.roles-permissions.toggle"), {
        role_id: roleId,
        permission_id: permissionId,
    });
};
</script>
<template lang="">
    <Head title="Gestione permessi"></Head>
    <AuthenticatedLayout section="roles">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h2>Gestione permessi</h2>
                </div>
            </div>
            <div class="row gy-3" v-for="role in roles" :key="role.id">
                <div class="col-12">
                    <h3>{{ role.display_name }}</h3>
                </div>
                <div class="col-12">
                    <div class="row">

                        <div class="col-md-12" v-for="permissions, group in groupedPermissions" :key="group">
                            <h4>{{ groupMap[group] }}</h4>

                            <div class="row">
                                <div class="col-md-3" v-for="permission in permissions" :key="permission.id">
                                    <input
                                        type="checkbox"
                                        class="form-check-inline"
                                        :checked="
                                            role.permissions.some(
                                                (p) => p.id === permission.id
                                            )
                                        "
                                        @change="
                                            togglePermission(role.id, permission.id)
                                        "
                                    />
                                    <label for="" class="form-check-label">{{
                                        permission.display_name
                                    }}</label>
                                </div>
                            </div>
                            <hr />
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;

h3 {
    color: $mainRed;
}
</style>
