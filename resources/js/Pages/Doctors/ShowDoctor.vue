<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { formatDate } from "@/utilities/formatDateFunction";
import Calendar from "@/Components/Calendar.vue";

import { ref } from "vue";

const props = defineProps({
    doctor: Object,
    doctors: Array,
    nurses: Array,
    nationalities: Array,
    patients: Array,
    userIsAdmin: Boolean
});

const detailsOpen = ref(false);

const showDetail = () => detailsOpen.value = !detailsOpen.value;

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
                <div class="col-12">
                    <div class="row">
                        <div class="col-12">
                            <h3>{{ doctor.user.name }} {{ doctor.user.surname }} <i class="fas" :class="detailsOpen == true ? 'fa-caret-up' : 'fa-caret-down'" @click="showDetail"></i></h3>
                        </div>
                        <div class="col-12" v-if="detailsOpen">
                            <div class="bg-main-red text-white">
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Anagrafica dottore</h3>
                                    </div>
                                    
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Data di nascita</label
                                        >
                                        <p>{{ formatDate(doctor.birthday) }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Città di nascita</label
                                        >
                                        <p>{{ doctor.birth_city }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Nazionalità</label>
                                        <p>
                                            {{
                                                doctor.nationality === null
                                                    ? "Nazionalità non valorizzata"
                                                    : doctor.nationality.name
                                            }}
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Sesso</label>
                                        <p>
                                            {{ doctor.genre === "m" ? "Uomo" : "Donna" }}
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Codice fiscale</label
                                        >
                                        <p>{{ doctor.personal_code }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Specializzazione</label
                                        >
                                        <p>{{ doctor.specialty.name }}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Residenza</h3>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Città di residenza</label
                                        >
                                        <p>{{ doctor.city }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Indirizzo</label>
                                        <p>{{ doctor.address }}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Informazioni contatto</h3>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Email</label>
                                        <p>{{ doctor.user.email }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Telefono</label>
                                        <p>{{ doctor.phone }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Pec</label>
                                        <p>{{ doctor.pec }}</p>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-md-7">
                    <Calendar
                        :appointments="doctor.appointments"
                        :userIsAdmin="userIsAdmin"
                        :doctors="doctors"
                        :nurses="nurses"
                        :patients="patients"
                        :doctor="doctor"
                    />
                </div>
                <div class="col-12 col-md-5">
                    <div class="bg-main-red">
                        <div class="row">
                            <div class="col-12">
                                <h3>Prestazioni</h3>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12 col-md-6">
                                <label for="" class="form-label">Prestazione</label>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="" class="form-label">Prezzo</label>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="" class="form-label">Durata</label>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="" class="form-label">Attivo</label>
                            </div>
                        </div>
                        <div class="row text-white" v-for="service in doctor.services" :key="service.id">
                            <div class="col-12 col-md-6">{{ service.name }}<br>{{ service.code}}</div>
                            <div class="col-12 col-md-2">{{ service.pivot.price }}€</div>
                            <div class="col-12 col-md-2">{{ service.pivot.duration_minutes}}</div>
                            <div class="col-12 col-md-2">
                                <span class="circle" :class="service.pivot.active ? 'is-active' : 'not-active'"><i class="fas" :class="service.pivot.active ? 'fa-check' : 'fa-times'"></i></span>
                            </div>
                            <hr>
                        </div>
                    </div>
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

.border-right-red {
    border-right: 3px solid $mainRed;
}

.circle {
    padding: 10px;
    background-color: #fff;
    border-radius: 50%;

    &.is-active {
        color: green;
    }

    &.not-active {
        color: $mainRed;
    }
}
</style>
