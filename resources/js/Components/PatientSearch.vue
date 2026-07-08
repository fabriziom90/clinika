<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const search = ref("");
const results = ref([]);

const searchPatients = async () => {
    const response = await axios.get(route('admin.patients.search'), {
        params: {
            search: search.value
        }
    })

    results.value = response.data;
}

const goToPatient = (id) => {
    router.visit(route("admin.patients.show", id));
}
</script>
<template lang="">
    <input
        v-model="search"
        type="text"
        class="form-control"
        placeholder="Nome, cognome o codice fiscale..."
        @input="searchPatients"
    >
    <div
        v-if="search.length > 0"
        class="mt-3"
    >
        <div
            v-if="results.length === 0"
            class="alert alert-light border"
        >
            Nessun paziente trovato.
        </div>
        <div
            v-else
            class="list-group"
        >
            <button
                v-for="patient in results"
                :key="patient.id"
                type="button"
                class="list-group-item list-group-item-action"
                @click="goToPatient(patient.id)"
            >
                <div class="fw-bold txt-main-red">
                    {{ patient.name }} {{ patient.surname }}
                </div>
                <small class="text-muted">
                    {{ patient.personal_code }}
                </small>
            </button>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use '../../scss/app.scss' as *;
@use '../../scss/_partials/variables' as *;

.patient-search {
    max-width: 700px;
}

.list-group-item {
    cursor: pointer;
}
</style>
