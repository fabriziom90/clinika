<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { formatDate } from "@/utilities/formatDateFunction";
import Calendar from "@/Components/Calendar.vue";
const props = defineProps({
    doctor: Object,
});

const appointments = [
    {
        title: "Visita Mario Rossi",
        start: "2025-02-10 10:00",
        end: "2025-02-10 10:30",
        color: "#4CAF50",
    },
    {
        title: "Controllo diabetico",
        start: "2025-02-12",
    },
];

const handleEventClick = (event) => {
    console.log("Hai cliccato l’appuntamento:", event);
};

const handleDateClick = (date) => {
    console.log("Hai cliccato la data:", date);
};
</script>
<template lang="">
    <Head title="Dettaglio dottore"></Head>
    <AuthenticatedLayout section="doctors">
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
                                :href="route('admin.doctors.edit', doctor.id)"
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
                            <p>{{ doctor.user.name }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Cognome</label>
                            <p>{{ doctor.user.surname }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Data di nascita</label
                            >
                            <p>{{ formatDate(doctor.birthday) }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Città di nascita</label
                            >
                            <p>{{ doctor.birth_city }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Nazionalità</label>
                            <p>
                                {{
                                    doctor.nationality === null
                                        ? "Nazionalità non valorizzata"
                                        : doctor.nationality.name
                                }}
                            </p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Sesso</label>
                            <p>
                                {{ doctor.genre === "m" ? "Uomo" : "Donna" }}
                            </p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Codice fiscale</label
                            >
                            <p>{{ doctor.personal_code }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Specializzazione</label
                            >
                            <p>{{ doctor.specialty.name }}</p>
                        </div>
                    </div>
                    <div class="row gy-3">
                        <div class="col-12">
                            <h3>Residenza</h3>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label"
                                >Città di residenza</label
                            >
                            <p>{{ doctor.city }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Indirizzo</label>
                            <p>{{ doctor.address }}</p>
                        </div>
                    </div>
                    <div class="row gy-3">
                        <div class="col-12">
                            <h3>Informazioni contatto</h3>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Email</label>
                            <p>{{ doctor.user.email }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Telefono</label>
                            <p>{{ doctor.phone }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="" class="form-label">Pec</label>
                            <p>{{ doctor.pec }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <Calendar
                        :events="appointments"
                        @event-click="handleEventClick"
                        @date-click="handleDateClick"
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
