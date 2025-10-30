<script setup>
import cities from "@/data/cities.json";
import { calculatePersonalCode } from "@/utilities/calculatePersonalCode";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    patient: Object,
});

const citiesList = ref(cities);
const birthSearch = ref("");
const residenceSearch = ref("");
const showBirthSuggestions = ref(false);
const showResidenceSuggestions = ref(false);
const $toast = useToast();

const form = useForm({
    name: props.patient.name,
    surname: props.patient.surname,
    email: props.patient.email,
    phone: props.patient.phone,
    genre: props.patient.genre,
    birthday: props.patient.birthday,
    birth_city: props.patient.birth_city,
    city: props.patient.city,
    address: props.patient.address,
    nationality_id: props.patient.nationality_id,
    personal_code: props.patient.personal_code,
});

// filter functions
const filteredBirthCities = computed(() => {
    const term = (form.birth_city || "").toLowerCase().trim();
    if (!term) return [];
    return citiesList.value
        .filter((c) => c.name.toLowerCase().includes(term))
        .slice(0, 10);
});

const filteredResidenceCities = computed(() => {
    const term = (form.city || "").toLowerCase().trim();
    if (!term) return [];
    return citiesList.value
        .filter((c) => c.name.toLowerCase().includes(term))
        .slice(0, 10);
});

const hideSuggestions = (type) => {
    setTimeout(() => {
        if (type === "birth") showBirthSuggestions.value = false;
        else if (type === "residence") showResidenceSuggestions.value = false;
    }, 200);
};

// update field when suggestion clicked
const selectBirthCity = (cityName) => {
    form.birth_city = cityName;
    birthSearch.value = cityName;
    showBirthSuggestions.value = false;
};

const selectResidenceCity = (cityName) => {
    form.city = cityName;
    residenceSearch.value = cityName;
    showResidenceSuggestions.value = false;
};

watch(
    () => [form.name, form.surname, form.genre, form.birthday, form.birth_city],
    () => {
        if (
            form.name &&
            form.surname &&
            form.genre &&
            form.birthday &&
            form.birth_city
        ) {
            const cf = calculatePersonalCode({
                name: form.name,
                surname: form.surname,
                sex: form.genre,
                birthDate: form.birthday,
                birthCity: form.birth_city,
                citiesList: citiesList.value,
            });
            if (cf && cf !== form.personal_code) {
                form.personal_code = cf;
            }
        }
    },
    { deep: true }
);

const handleSubmitForm = () => {
    form.put(route(`admin.patients.update`, { patient: props.patient.id }), {
        onError: (errors) => {
            console.log(errors);
            $toast.error("Errore durante il salvataggio", {
                position: "top-right",
                duration: 3000,
            });
        },
    });
};
</script>
<template lang="">
    <Head title="Modifica paziente"></Head>
    <AuthenticatedLayout section="patients">
        <div>
            <h2>Modifica paziente</h2>
        </div>
        <form @submit.prevent="handleSubmitForm" class="mt-4">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Nome</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Inserisci nome"
                        name="name"
                        id="name"
                        v-model="form.name"
                        required
                    />
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Cognome</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Inserisci cognome"
                        name="surname"
                        id="surname"
                        v-model="form.surname"
                        required
                    />
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Email</label>
                    <input
                        type="mail"
                        class="form-control"
                        placeholder="Inserisci email"
                        name="email"
                        id="email"
                        v-model="form.email"
                        required
                    />
                </div>
                <div class="col-12 col-md-4">
                    <div class="label form-label">Telefono</div>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        class="form-control"
                        placeholder="Inserisci telefono"
                        v-model="form.phone"
                        required
                    />
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Sesso</label>
                    <select
                        name="genre"
                        id="genre"
                        class="form-select"
                        v-model="form.genre"
                        required
                    >
                        <option value="">Seleziona sesso</option>
                        <option value="m">Uomo</option>
                        <option value="d">Donna</option>
                    </select>
                </div>
            </div>
            <hr />
            <div class="row g-4">
                <div class="col-12">
                    <h3>Sezione nascita</h3>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Data di nascita</label>
                    <input
                        type="date"
                        class="form-control"
                        placeholder="Inserisci data di nascita"
                        name="birth"
                        id="birth"
                        v-model="form.birthday"
                        required
                    />
                </div>
                <div class="col-12 col-md-4 position-relative">
                    <label for="" class="form-label">Città di nascita</label>
                    <input
                        type="text"
                        class="form-control"
                        v-model="form.birth_city"
                        placeholder="Inserisci città di nascita"
                        @focus="showBirthSuggestions = true"
                        @blur="hideSuggestions('birth')"
                        required
                    />

                    <ul
                        class="list-group position-absolute w-100 z-10"
                        v-if="
                            showBirthSuggestions && filteredBirthCities.length
                        "
                    >
                        <li
                            v-for="c in filteredBirthCities"
                            :key="c.name"
                            class="list-group-item list-group-item-action"
                            @click="
                                form.birth_city = c.name;
                                showBirthSuggestions = false;
                            "
                        >
                            {{ c.name }} ({{ c.province.name }})
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Nazionalità</label>
                    <select
                        name="nationality"
                        id="nationality"
                        class="form-select"
                        v-model="form.nationality_id"
                        required
                    >
                        <option value="">Seleziona nazionalità</option>
                        <option
                            :value="nationality.id"
                            v-for="nationality in nationalities"
                            :key="nationality.name"
                        >
                            {{ nationality.name }}
                        </option>
                    </select>
                </div>
            </div>
            <hr />
            <div class="row g-4">
                <div class="col-12">
                    <h3>Sezione residenza</h3>
                </div>
                <div class="col-12 col-md-4 position-relative">
                    <label for="" class="form-label">Città di residenza</label>
                    <input
                        type="text"
                        class="form-control"
                        v-model="form.city"
                        placeholder="Inserisci città di residenza"
                        @focus="showResidenceSuggestions = true"
                        @blur="hideSuggestions('residence')"
                        required
                    />

                    <ul
                        class="list-group position-absolute w-100 z-10"
                        v-if="
                            showResidenceSuggestions &&
                            filteredResidenceCities.length
                        "
                    >
                        <li
                            v-for="c in filteredResidenceCities"
                            :key="c.name"
                            class="list-group-item list-group-item-action"
                            @click="
                                form.city = c.name;
                                showResidenceSuggestions = false;
                            "
                        >
                            {{ c.name }} ({{ c.province.name }})
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Indirizzo</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Inserisci indirizzo"
                        name="address"
                        id="address"
                        v-model="form.address"
                        required
                    />
                </div>
                <div class="col-12 col-md-4">
                    <label for="" class="form-label">Codice fiscale</label>
                    <input
                        type="text"
                        name="personal_code"
                        id="personal_code"
                        class="form-control"
                        placeholder="Inserisci codice fiscale"
                        v-model="form.personal_code"
                        required
                    />
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="main-button">Salva</button>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;
hr {
    color: $mainRed;
    border-width: 5px;
    margin: 30px 0px;
}
</style>
