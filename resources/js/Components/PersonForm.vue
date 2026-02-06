<script setup>
import cities from "@/data/cities.json";
import { calculatePersonalCode } from "@/utilities/calculatePersonalCode";
import { ref, computed, watch, onMounted, defineEmits } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    specialties: { type: Array, default: () => [] },
    services: Array,
    formType: { type: String, default: "patient" },
    person: { type: Object, default: null },
    inlineMode: { type: Boolean, default: false },
});

const emit = defineEmits(["savedInline"]);

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
    services: [{
        service_id: "",
        price: "",
        duration: "",
        active: 1
    }],
    user_id: "",
    inline: false,
});

onMounted(() => {
    if (!props.person) return;
    const person = props.person;

    if (person.services && person.services.length) {
        form.services = person.services.map(s => ({
            service_id: s.id,
            name: s.name,
            price: s.pivot?.price ?? 0,
            duration: s.pivot?.duration_minutes ?? 0,
            active: s.pivot?.active ?? 1
        }));
    }

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
    // form.services = person.services ?? [];
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

const filteredServices = computed(() => {
    const specialty = props.specialties.find(s => s.id === form.specialty_id);
    return specialty ? specialty.services : [];
})

const hideSuggestions = (type) => {
    setTimeout(() => {
        if (type === "birth") showBirthSuggestions.value = false;
        else if (type === "residence") showResidenceSuggestions.value = false;
    }, 200);
};

// Add service row 
const addRow = () => {
    form.services.push({
        name: "",
        duration: "",
        price: "",
        active: 1,
    });
};

// remove service row
const removeRow = (index) => {
    if (form.services.length > 1) {
        form.services.splice(index, 1);
    }
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

watch(() => form.specialty_id, (newVal, oldVal) => {
     if (!props.person) {
        form.services = [{
            service_id: "",
            price: "",
            duration: "",
            active: 1
        }]
    }
})

const onServiceChange = (index) => {
    const row = form.services[index]
    if (!row.service_id) return

    const selected = filteredServices.value.find(
        s => s.id === row.service_id
    )
    
    if (selected) {
        row.duration = selected.default_duration
        row.price = selected.default_price
    }
}

const handleSubmitForm = () => {
    onSaving.value = true;
    const isEdit = props.person ? true : false;

    const routeName = isEdit
        ? `admin.${props.formType}s.update`
        : `admin.${props.formType}s.store`;

    const url = isEdit ? route(routeName, props.person.id) : route(routeName);

    // inline form: from modal
    if (props.inlineMode) {
        (form.inline = true),
            form.post(url, {
                data: { inline: true },
                onSuccess: (page) => {
                    onSaving.value = false;
                    const newPerson = page.props.newPerson;
                    emit("savedInline", newPerson);
                },
                onError: (err) => {
                    const flat = Object.values(err).flat();
                    $toast.error(flat.join("\n"));
                    onSaving.value = false;
                },
            });

        return; // blocca redirect modalità normale
    }

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
                <span v-if="form.errors.name" class="text-danger">{{
                    form.errors.name
                }}</span>
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
                <span v-if="form.errors.surname" class="text-danger">{{
                    form.errors.surname
                }}</span>
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
                <span v-if="form.errors.email" class="text-danger">{{
                    form.errors.email
                }}</span>
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
                <span v-if="form.errors.phone" class="text-danger">{{
                    form.errors.phone
                }}</span>
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
                <span v-if="form.errors.genre" class="text-danger">{{
                    form.errors.genre
                }}</span>
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
                <span v-if="form.errors.birthday" class="text-danger">{{
                    form.errors.birthday
                }}</span>
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
                <span v-if="form.errors.birth_city" class="text-danger">{{
                    form.errors.birth_city
                }}</span>
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
                <span v-if="form.errors.nationality_id" class="text-danger">{{
                    form.errors.nationality_id
                }}</span>
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
                <span v-if="form.errors.city" class="text-danger">{{
                    form.errors.city
                }}</span>
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
                <span v-if="form.errors.address" class="text-danger">{{
                    form.errors.address
                }}</span>
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
                <span v-if="form.errors.personal_code" class="text-danger">{{
                    form.errors.personal_code
                }}</span>
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
                <span v-if="form.errors.vat" class="text-danger">{{
                    form.errors.vat
                }}</span>
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
                <span v-if="form.errors.pec" class="text-danger">{{
                    form.errors.pec
                }}</span>
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
                <span v-if="form.errors.specialty_id" class="text-danger">{{
                    form.errors.specialty_id
                }}</span>
            </div>
            <div class="col-12" v-if="formType === 'doctor'">
                <hr>
                <h3>Prestazioni medico</h3>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="" class="form-label"><strong>Prestazione</strong></label>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label"><strong>Prezzo</strong></label>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label"><strong>Durata (minuti)</strong></label>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label"><strong>Attivo</strong></label>
                    </div>
                </div>
                <div v-for="service, index in form.services" :key="index" class="row gy-3 mb-3">
                    <div class="row gy-2">
                        <div class="col-md-4">
                            <select name="" id="" class="form-select" v-model="service.service_id" @change="onServiceChange(index)">
                                <option value="">Seleziona una specializzazione prima</option>
                                <option :value="service.id" v-for="service in filteredServices" :key="service.id">{{ service.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="number" placeholder="Prezzo" v-model="service.price" />
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="number" placeholder="Durata" v-model="service.duration" />
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" v-model="service.active">
                                <option :value="1">Attiva</option>
                                <option :value="0">Disattiva</option>
                            </select>
                        </div>
                        <div class="col-md-2">    
                            <button
                                v-if="form.services.length > 1"
                                class="main-button"
                                @click="removeRow(index)"
                                type="button"
                            >
                                Rimuovi
                            </button>
                        </div>
                    </div>

                    
                </div>
                <button class="secondary-button mb-3" type="button" @click="addRow">
                    + Aggiungi prestazione
                </button>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 col-md-4">
                <button type="submit" class="main-button">
                    <span v-if="onSaving">
                        Salvataggio in corso...
                        <i class="fa-solid fa-spinner fa-spin ms-2"></i>
                    </span>
                    <span v-else>Salva </span>
                </button>
            </div>
        </div>
    </form>
</template>
<style lang="scss" scoped>
@use "../../scss/app.scss" as *;
</style>
