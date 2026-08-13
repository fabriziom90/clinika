<script setup>
import { ref, watch } from 'vue';
import { useConfigStore } from "@/stores/main";
import MedicalEntryCard from './MedicalEntryCard.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// Props
const props = defineProps({
  patient: { type: Object, required: true },
});

// Pinia store
const configStore = useConfigStore();

const { user } = configStore;
// Stato interno
const appointments = ref([...props.patient.appointments]);


</script>
<template>
  <div>
    <div class="d-flex justify-content-between">
      <h2>Cartella Clinica</h2>

    </div>

    <div v-if="appointments.length === 0">
      Nessuna visita disponibile.
    </div>
    <div v-else>
      <MedicalEntryCard v-for="appointment in appointments" :key="appointment.id" :appointment="appointment"
        :patient="patient" :user="user" />
    </div>

  </div>
</template>

<style scoped>
.medical-record-timeline {
  max-width: 800px;
  margin: auto;
}
</style>
