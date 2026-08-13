<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { formatDate } from "@/utilities/formatDateFunction";
import Calendar from "@/Components/Calendar.vue";

const props = defineProps({
    appointments: Array,
    doctor: Object,
    nurse: Object,
    doctors: Array,
    patients: Array,
    nurses: Array,
    nationalities: Array,
    userCanCreateAppointment: Boolean,
});


</script>
<template lang="">
    <Head title="Dettaglio infermiere"></Head>
    <AuthenticatedLayout section="nurses">
        <div class="container-fluid">
            <div class="row gy-5">
                <div class="col-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2>Dettaglio dottore</h2>
                        </div>
                        <div>
                            <Link
                                class="main-button"
                                :href="route('admin.nurses.edit', nurse.id)"
                                >Modifica dottore</Link
                            >
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4 border-right-red">
                    <div class="row gy-3">
                        <div class="col-12">
                            <h3>Anagrafica dottore</h3>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Nome</label>
                            <p>{{ nurse.user.name }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Cognome</label>
                            <p>{{ nurse.user.surname }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Data di nascita</label
                            >
                            <p>{{ formatDate(nurse.birthday) }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Città di nascita</label
                            >
                            <p>{{ nurse.birth_city }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Nazionalità</label>
                            <p>
                                {{
                                    nurse.nationality === null
                                        ? "Nazionalità non valorizzata"
                                        : nurse.nationality.name
                                }}
                            </p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Sesso</label>
                            <p>
                                {{ nurse.genre === "m" ? "Uomo" : "Donna" }}
                            </p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Codice fiscale</label
                            >
                            <p>{{ nurse.personal_code }}</p>
                        </div>
                        <!-- <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Specializzazione</label
                            >
                            <p>{{ nurse.specialty.name }}</p>
                        </div> -->
                    </div>
                    <div class="row gy-3">
                        <div class="col-12">
                            <h3>Residenza</h3>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Città di residenza</label
                            >
                            <p>{{ nurse.city }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Indirizzo</label>
                            <p>{{ nurse.address }}</p>
                        </div>
                    </div>
                    <div class="row gy-3">
                        <div class="col-12">
                            <h3>Informazioni contatto</h3>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Email</label>
                            <p>{{ nurse.user.email }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Telefono</label>
                            <p>{{ nurse.phone }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Pec</label>
                            <p>{{ nurse.pec }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <Calendar
                        :appointments="nurse.appointments"
                        :nurse="nurse"
                        :nurses="[nurse]"
                        :userCanCreateAppointment="false"
                    />
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

.border-right-red {
    border-right: 3px solid $mainRed;
}
</style>
