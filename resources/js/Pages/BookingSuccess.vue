<script setup>
import { Head } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>

    <Head title="Prenotazione effettuata" />

    <header class="bg-main-red">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between py-3">
                        <div>
                            <ApplicationLogo width="75px" color="#fff" title="Clinika - Prenotazione appuntamento" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="mt-5">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-12 col-md-8 col-lg-6">

                    <div class="text-center">

                        <div class="mb-4">
                            <i class="fa-solid fa-circle-check fa-4x txt-main-red"></i>
                        </div>

                        <h1 class="txt-main-red mb-3">
                            Prenotazione effettuata
                        </h1>

                        <p class="fs-5">
                            La tua prenotazione è stata registrata
                            correttamente.
                        </p>

                        <p>
                            Riceverai una mail di conferma all'indirizzo
                            indicato durante la prenotazione.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </main>

</template>
