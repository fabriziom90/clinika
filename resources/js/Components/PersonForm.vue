<script setup>
import cities from "@/data/cities.json";
import { calculatePersonalCode } from "@/utilities/calculatePersonalCode";
import { ref, computed, watch, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    specialties: { type: Array, default: () => [] },
    formType: { type: String, default: "patient" },
    person: { type: Object, default: null },
});

const onSaving = ref(false);
const citiesList = ref(cities);
const birthSearch = ref("");
const residenceSearch = ref("");
const showBirthSuggestions = ref(false);
const showResidenceSuggestions = ref(false);
const $toast = useToast();

const routeMap = {
    doctor: "admin.doctors",
    nurse: "admin.nurses",
    patient: "admin.patients",
    operator: "admin.operators",
};

const form = useForm({
    name: "",
    surname: "",
    email: "",
    phone: "",
    genre: "",
    birthday: "",
    birth_city: "",
    city: "",
    address: "",
    nationality_id: "",
    personal_code: "",
    pec: "",
    vat: "",
    specialty_id: "",
    user_id: "",
});

onMounted(() => {
    if (!props.person) return;
    const person = props.person;

    form.defaults({ ...person });

    // if p.user exists then it's doctor or nurse
    // otherwise it's patient
    const source = person.user ?? person;

    form.name = source.name ?? "";
    form.surname = source.surname ?? "";
    form.email = source.email ?? "";
    form.phone = person.phone ?? "";
    form.genre = person.genre ?? "";
    form.birthday = person.birthday ?? "";
    form.birth_city = person.birth_city ?? "";
    form.city = person.city ?? "";
    form.address = person.address ?? "";
    form.nationality_id = person.nationality_id ?? "";
    form.personal_code = person.personal_code ?? "";
    form.pec = person.pec ?? "";
    form.vat = person.vat ?? "";
    form.specialty_id = person.specialty_id ?? "";
    form.user_id = person.user_id ?? "";
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
    onSaving.value = true;
    const isEdit = props.person ? true : false;

    const routeName = isEdit
        ? `admin.${props.formType}s.update`
        : `admin.${props.formType}s.store`;

    const url = isEdit ? route(routeName, props.person.id) : route(routeName);

    form[isEdit ? "put" : "post"](url, {
        onSuccess: () => {
            onSaving.value = false;
        },
        onError: (err) => {
            const flat = Object.values(err).flat();
            $toast.error(flat.join("\n"));
            onSaving.value = false;
        },
    });
};
</script>
<template lang="">
    <form @submit.prevent="handleSubmitForm" class="mt-4">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="" class="form-label">Nome</label>
                <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.name }"
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
                    :class="{ 'is-invalid': form.errors.surname }"
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
                    :class="{ 'is-invalid': form.errors.email }"
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
                    :class="{ 'is-invalid': form.errors.phone }"
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
                    :class="{ 'is-invalid': form.errors.genre }"
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
                    :class="{ 'is-invalid': form.errors.birthday }"
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
                    :class="{ 'is-invalid': form.errors.birth_city }"
                    v-model="form.birth_city"
                    placeholder="Inserisci città di nascita"
                    @focus="showBirthSuggestions = true"
                    @blur="hideSuggestions('birth')"
                    required
                />

                <ul
                    class="list-group position-absolute w-100 z-10"
                    v-if="showBirthSuggestions && filteredBirthCities.length"
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
                    :class="{ 'is-invalid': form.errors.nationality_id }"
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
                    :class="{ 'is-invalid': form.errors.city }"
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
                    :class="{ 'is-invalid': form.errors.address }"
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
                    :class="{ 'is-invalid': form.errors.personal_code }"
                    placeholder="Inserisci codice fiscale"
                    v-model="form.personal_code"
                    required
                />
            </div>
        </div>
        <hr />
        <div
            class="row g-4"
            v-if="formType === 'doctor' || formType === 'nurse'"
        >
            <div class="col-12">
                <h3>Sezione dati professionali</h3>
            </div>
            <div class="col-md-4">
                <label class="form-label">Partita IVA</label>
                <input
                    v-model="form.vat"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.vat }"
                    name="vat"
                    id="vat"
                    placeholder="Inserisci Partita IVA"
                />
            </div>
            <div class="col-md-4">
                <label class="form-label">PEC</label>
                <input
                    v-model="form.pec"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.pec }"
                    name="pec"
                    id="pec"
                    placeholder="Inserisci PEC"
                />
            </div>

            <!-- Solo per medici -->
            <div class="col-md-4" v-if="formType === 'doctor'">
                <label class="form-label">Specializzazione</label>
                <select
                    v-model="form.specialty_id"
                    class="form-select"
                    :class="{ 'is-invalid': form.errors.specialty_id }"
                >
                    <option value="">Seleziona</option>
                    <option v-for="s in specialties" :key="s.id" :value="s.id">
                        {{ s.name }}
                    </option>
                </select>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 col-md-4">
                <button type="submit" class="main-button">
                    <span v-if="onSaving">
                        "Salvataggio in corso...
                        <i class="fa-solid fa-spinner fa-spin ms-2"></i> :
                    </span>
                    <span v-else> Salva </span>
                </button>
            </div>
        </div>
    </form>
</template>
<style lang="scss" scoped>
@use "../../scss/app.scss" as *;
</style>
