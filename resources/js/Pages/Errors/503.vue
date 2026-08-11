<script setup>
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>

    <Head title="Servizio non disponibile" />

    <div class="vh-100 vw-100 d-flex justify-content-center align-items-center flex-column">

        <div class="text-center">

            <h1 class="text-mainred font-size-50">
                503
            </h1>

            <h2 class="font-size-40">
                Servizio non disponibile
            </h2>

            <p class="mb-2">
                Clinika non è momentaneamente disponibile.
            </p>

            <p class="text-muted mb-5">
                Il servizio potrebbe essere temporaneamente in manutenzione.
                Riprova tra qualche momento.
            </p>

            <Link :href="route('admin.dashboard')" class="main-button">
                <i class="fa-solid fa-rotate-right me-2"></i>
                Riprova
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
