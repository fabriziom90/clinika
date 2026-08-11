<script setup>
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>

    <Head title="Sessione scaduta" />

    <div class="vh-100 vw-100 d-flex justify-content-center align-items-center flex-column">

        <div class="text-center">

            <h1 class="text-mainred font-size-50">
                419
            </h1>

            <h2 class="font-size-40">
                Sessione scaduta
            </h2>

            <p class="mb-2">
                La sessione è scaduta o la richiesta non è più valida.
            </p>

            <p class="text-muted mb-5">
                Ricarica la pagina e riprova.
            </p>

            <Link :href="route('login')" class="main-button">
                <i class="fa-solid fa-rotate-right me-2"></i>
                Torna al login
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
