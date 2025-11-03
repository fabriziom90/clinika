<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    roles: Array,
    permissions: Array,
});

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
            <div class="row gy-3" v-for="role in roles">
                <div class="col-12">
                    <h3>{{ role.display_name }}</h3>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-3" v-for="permission in permissions">
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
                </div>
                <hr />
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
