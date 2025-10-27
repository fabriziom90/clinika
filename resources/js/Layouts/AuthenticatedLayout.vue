<script setup>
import TopBar from "@/Components/TopBar.vue";
import Sidebar from "@/Components/Sidebar.vue";

const props = defineProps({
    section: "patients" | "doctors" | "nurses" | "superadmin",
});
</script>

<template>
    <div class="vw-100 vh-100">
        <TopBar />
        <main>
            <Sidebar
                :current-section="props.section"
                :user-role="$page.props.auth.user.roles?.[0]?.name || ''"
            />
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
        padding: 10px;
        overflow-y: auto;
        width: calc(100% - 250px);
    }
}
</style>
