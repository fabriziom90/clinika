<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    status: {
        type: Number,
        default: 403,
    },

    message: {
        type: String,
        default: "Non sei autorizzato ad accedere a questa pagina.",
    },
});

const clinicUnavailable = props.message === "La clinica non è più attiva.";
</script>

<template>

    <Head :title="clinicUnavailable ? 'Clinica non disponibile' : 'Accesso negato'" />

    <div class="vh-100 vw-100 d-flex justify-content-center align-items-center flex-column">
        <div class="text-center">

            <h1 class="text-mainred font-size-50">
                {{ status }}
            </h1>

            <template v-if="clinicUnavailable">

                <h2 class="font-size-40">
                    Clinica non disponibile
                </h2>

                <p class="mb-2">
                    La clinica a cui stai tentando di accedere non è attualmente attiva.
                </p>

                <p class="text-muted mb-5">
                    Contatta l'amministratore della piattaforma per maggiori informazioni.
                </p>

            </template>

            <template v-else>

                <h2 class="font-size-40">
                    Accesso negato
                </h2>

                <p class="mb-5">
                    Non hai i permessi per accedere a questa pagina
                </p>

                <Link :href="route('admin.dashboard')" class="main-button">
                    Torna alla Dashboard
                </Link>

            </template>

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
