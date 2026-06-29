<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref } from "vue";
import MedicalRecordTimeline from "@/Components/MedicalRecordTimeline.vue";

const props = defineProps({
    patient: Object,
});

const detailsOpen = ref(false);

const showDetail = () => (detailsOpen.value = !detailsOpen.value);

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const d = String(date.getDate()).padStart(2, "0");
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const y = date.getFullYear();
    return `${d}/${m}/${y}`;
};
</script>
<template>

    <Head title="Dettaglio paziente"></Head>
    <AuthenticatedLayout section="patients">
        <div class="container-fluid">
            <div class="row gy-3">
                <div class="col-12">
                    <div class="d-flex
  justify-content-between align-items-center">
                        <div>
                            <h2>Dettaglio paziente</h2>
                        </div>
                        <div>
                            <Link class="main-button" :href="route('admin.patients.edit', patient.id)">Modifica paziente
                            </Link>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="col-12">
                    <div class="row">
                        <div class="col-12">
                            <h3>{{ patient.name }} {{ patient.surname }} <i class="fas"
                                    :class="detailsOpen == true ? 'fa-caret-up' : 'fa-caret-down'"
                                    @click="showDetail"></i></h3>
                        </div>
                        <div class="col-12" v-if="detailsOpen">
                            <div class="bg-main-red text-white">
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Anagrafica dottore</h3>
                                    </div>
                                    <div class="col-12 col-md-2"> <label for="" class="form-label">Data di
                                            nascita</label>
                                        <p>{{ formatDate(patient.birthday) }}</p>
                                    </div>
                                    <div class="col-12 col-md-2"> <label for="" class="form-label">Città di
                                            nascita</label>
                                        <p>{{ patient.birth_city }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Nazionalità</label>
                                        <p>
                                            {{
                                                patient.nationality === null
                                                    ? "Nazionalità non valorizzata"
                                                    : patient.nationality.name
                                            }}
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-2"> <label for="" class="form-label">Sesso</label>
                                        <p>
                                            {{ patient.genre === "m" ? "Uomo" : "Donna" }}
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-2"> <label for="" class="form-label">Codice
                                            fiscale</label>
                                        <p>{{ patient.personal_code }}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row
  gy-3">
                                    <div class="col-12">
                                        <h3>Residenza</h3>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Città di residenza</label>
                                        <p>{{ patient.city }}</p>
                                    </div>
                                    <div class="col-12 col-md-2"> <label for="" class="form-label">Indirizzo</label>
                                        <p>{{ patient.address }}</p>
                                    </div>
                                    <div class="col-12 col-md-2"><label for="" class="form-label">CAP</label>
                                        <p>{{ patient.zip_code }}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Informazioni contatto</h3>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Email</label>
                                        <p>{{ patient.email }}</p>
                                    </div>
                                    <div class="col-12 col-md-2"> <label for="" class="form-label">Telefono</label>
                                        <p>{{
                                            patient.phone
                                        }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <MedicalRecordTimeline :patient="patient" />
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

.bg-main-red {
    padding: 20px;

    h3,
    label,
    hr {
        color: #fff;
    }
}

label {
    border-bottom: 1px solid $mainRed;
    font-weight: bold;
}
</style>
