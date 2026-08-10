<script setup>
import TopBar from "@/Components/TopBar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import { useGlobalToast } from "@/composables/useGlobalToast";

useGlobalToast();

const props = defineProps({
    section:
        "patients" |
        "doctors" |
        "nurses" |
        "secretaries" |
        "roles" |
        "specialties" |
        "clinicrooms" |
        "products" |
        "drugs" |
        "admin" |
        "superadmin"
});
</script>

<template>
    <div class="vw-100 vh-100">
        <TopBar />
        <main>
            <Sidebar :current-section="props.section"
                :userRole="$page.props.auth.user.is_superadmin ? 'superadmin' : ($page.props.auth.user.roles[0] || '')" />
            <div class="main-content">
                <slot />
            </div>
        </main>
    </div>
</template>

<style lang="scss" scoped>
main {
    display: flex;

    height: calc(100% - 100px);

    .main-content {
        padding: 20px;
        overflow-y: auto;
        width: calc(100% - 250px);
    }
}
</style>
