<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { formatDate } from "@/utilities/formatDateFunction";
import Calendar from "@/Components/Calendar.vue";

import { ref } from "vue";

const props = defineProps({
    secretary: Object,
    nationalities: Array,
    userIsAdmin: Boolean
});


</script>
<template lang="">
    <Head title="Dettaglio segretaria"></Head>
    <AuthenticatedLayout section="secretaries">
        <div class="container-fluid">
            <div class="row gy-5">
                <div class="col-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2>Dettaglio segretaria</h2>
                        </div>
                        <div>
                            <Link
                                class="main-button"
                                :href="route('admin.secretaries.edit', secretary.id)"
                                >Modifica segretaria</Link
                            >
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-12">
                            <h3>{{ secretary.user.name }} {{ secretary.user.surname }} </h3>
                        </div>
                        <div class="col-12">
                            <div class="bg-main-red text-white">
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Anagrafica segretaria</h3>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Data di nascita</label
                                        >
                                        <p>{{ formatDate(secretary.birthday) }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Città di nascita</label
                                        >
                                        <p>{{ secretary.birth_city }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Nazionalità</label>
                                        <p>
                                            {{
                                                secretary.nationality === null
                                                    ? "Nazionalità non valorizzata"
                                                    : secretary.nationality.name
                                            }}
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Sesso</label>
                                        <p>
                                            {{ secretary.genre === "m" ? "Uomo" : "Donna" }}
                                        </p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label"
                                            >Codice fiscale</label
                                        >
                                        <p>{{ secretary.personal_code }}</p>
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
                                        <p>{{ secretary.city }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Indirizzo</label>
                                        <p>{{ secretary.address }}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Informazioni contatto</h3>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Email</label>
                                        <p>{{ secretary.user.email }}</p>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="" class="form-label">Telefono</label>
                                        <p>{{ secretary.phone }}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        {{ secretary.notes}}
                                    </div>
                                </div>
                            </div>

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
