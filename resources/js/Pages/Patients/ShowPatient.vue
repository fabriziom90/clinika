<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { formatDate } from "@/utilities/formatDateFunction";

const props = defineProps({
    patient: Object,
});
</script>
<template lang="">
    <Head title="Dettaglio paziente"></Head>
    <AuthenticatedLayout section="patients">
        <div class="container-fluid">
            <div class="row gy-3">
                <div class="col-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2>Dettaglio paziente</h2>
                        </div>
                        <div>
                            <Link
                                class="main-button"
                                :href="route('admin.patients.edit', patient.id)"
                                >Modifica paziente</Link
                            >
                        </div>
                    </div>
                </div>
                <hr />
                <div class="col-12">
                    <h3>Anagrafica paziente</h3>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Nome</label>
                    <p>{{ patient.name }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Cognome</label>
                    <p>{{ patient.surname }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Data di nascita</label>
                    <p>{{ formatDate(patient.birthday) }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Città di nascita</label>
                    <p>{{ patient.birth_city }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Nazionalità</label>
                    <p>
                        {{
                            patient.nationality === null
                                ? "Nazionalità non valorizzata"
                                : patient.nationality.name
                        }}
                    </p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Sesso</label>
                    <p>{{ patient.genre === "m" ? "Uomo" : "Donna" }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Codice fiscale</label>
                    <p>{{ patient.personal_code }}</p>
                </div>
            </div>
            <hr />
            <div class="row gy-3">
                <div class="col-12">
                    <h3>Residenza</h3>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Città di residenza</label>
                    <p>{{ patient.city }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Indirizzo</label>
                    <p>{{ patient.address }}</p>
                </div>
            </div>
            <hr />
            <div class="row gy-3">
                <div class="col-12">
                    <h3>Informazioni contatto</h3>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Email</label>
                    <p>{{ patient.email }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Telefono</label>
                    <p>{{ patient.phone }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;

h3,
label {
    color: $mainRed;
}

label {
    border-bottom: 1px solid $mainRed;
    font-weight: bold;
}
</style>
