<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import CreatePatientHealthHistoryModal from './CreatePatientHealthHistoryModal.vue';
import PatientHealthHistoryTimelineModal from './PatientHealthHistoryTimelineModal.vue';

const props = defineProps({
    patient: Object,
    histories: Array,
    canEdit: Boolean
})

const showModal = ref(false);
const showHistoryModal = ref(false);
const showCompareModal = ref(false);
const selected = ref([]);

const currentHistory = computed(() => props.histories.find(history => history.is_current))

const refresh = () => {
    showModal.value = false;

    router.reload({
        only: ["patient"],
        preserveScroll: true
    })
}

</script>
<template lang="">
    <div class="card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5>Anamnesi paziente</h5>

            <div>
            <button
                class="main-button me-2"
                @click="showHistoryModal = true"
            >
                Visualizza storico
            </button>
            <button
                v-if="canEdit"
                class="main-button"
                @click="showModal = true"
            >
                Aggiorna anamnesi
            </button>
            </div>
        </div>

        <!-- CURRENT -->
        <div v-if="currentHistory">

            <p><strong>Allergie:</strong> {{ currentHistory.allergies }}</p>
            <p><strong>Patologie croniche:</strong> {{ currentHistory.chronic_diseases }}</p>
            <p><strong>Terapie in corso:</strong> {{ currentHistory.current_therapies }}</p>
            <p><strong>Chirurgia:</strong> {{ currentHistory.surgical_history }}</p>
            <p><strong>Famiglia:</strong> {{ currentHistory.family_history }}</p>
            <p><strong>Stile di vita:</strong> {{ currentHistory.lifestyle }}</p>
            <p><strong>Vaccinazioni:</strong> {{ currentHistory.vaccinations }}</p>
            <p><strong>Note:</strong> {{ currentHistory.notes }}</p>

        </div>

        <div v-else>
            Nessuna anamnesi disponibile
        </div>

    </div>
    <PatientHealthHistoryTimelineModal
        :show="showHistoryModal"
        :histories="histories"
        @close="showHistoryModal = false"
    />
    <CreatePatientHealthHistoryModal
        :show="showModal"
        :patient="patient"
        :history="currentHistory"
        @close="showModal = false"
        @saved="refresh"
    />
    <PatientHealthHistoryTimelineModal
        :show="showCompareModal"
        :left-history="selected[0]"
        :right-history="selected[1]"
        @close="showCompareModal = false"
    />
</template>
<style lang="">

</style>
