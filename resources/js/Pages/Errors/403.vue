<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    status: {
        type: Number,
        default: 403,
    },
    message: {
        type: String,
        default: "Non sei autorizzato ad accedere a questa pagina.",
    },
});
</script>

<template>

    <Head title="Accesso negato" />
    <div class="vh-100 vw-100 d-flex justify-content-center align-items-center flex-column">
        <div>
            <h1 class="text-mainred font-size-50">403</h1>
            <h2 class="font-size-40">Accesso negato</h2>
            <p class="mb-5">Non hai i permessi per accedere a questa sezione</p>
            <Link :href="route('admin.dashboard')" class="main-button">
                Torna alla Dashboard
            </Link>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;

.text-mainred {
    color: $mainRed;
}

.font-size-50 {
    font-size: 50px;
}

.font-size-40 {
    font-size: 40px;
}
</style>
