import { defineStore } from "pinia";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export const useConfigStore = defineStore("config", () => {
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const hasRole = (roles) => {
        if (!user.value?.roles) {
            return false;
        }

        if (Array.isArray(roles)) {
            return roles.some((role) => user.value.roles.includes(role));
        }

        return user.value.roles.includes(roles);
    };
    const hasPermission = (permission) =>
        user.value?.permissions?.includes(permission);

    const canAny = (...permissions) => {
        return permissions.some((permission) => hasPermission(permission));
    };

    const canAll = (...permissions) => {
        return permissions.every((permission) => hasPermission(permission));
    };

    return {
        apiBaseUrl: "http://127.0.0.1:3000",
        user,
        hasRole,
        hasPermission,
        canAny,
        canAll,
    };
});
