<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    status: {
        type: Number,
        default: 500,
    },

    message: {
        type: String,
        default: "Si è verificato un errore durante l'elaborazione della richiesta.",
    },
});
</script>

<template>

    <Head title="Errore interno" />

    <div class="vh-100 vw-100 d-flex justify-content-center align-items-center flex-column">

        <div class="text-center">

            <h1 class="text-mainred font-size-50">
                500
            </h1>

            <h2 class="font-size-40">
                Si è verificato un errore
            </h2>

            <p class="mb-2">
                Qualcosa è andato storto durante l'elaborazione della richiesta.
            </p>

            <p class="text-muted mb-5">
                Riprova tra qualche momento. Se il problema persiste, contatta l'amministratore.
            </p>

            <Link :href="route('admin.dashboard')" class="main-button">
                <i class="fa-solid fa-house me-2"></i>
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
