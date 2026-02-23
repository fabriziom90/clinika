<script setup>
import CreateMedicalEntryModal from '../Pages/Patients/CreateMedicalEntryModal.vue'
import { ref, computed, watch } from 'vue';
import { useConfigStore } from '@/stores/main';
const props = defineProps({
    appointment: Object,
    patient: Object,
    canEdit: Boolean,
    canDelete: Boolean,
})

const medicalAppointment = ref(props.appointment);
watch(() => props.appointment,
    (newAppointment) => medicalAppointment.value = newAppointment), { deep: true };

const store = useConfigStore();

const user = computed(() => store.user);

const showModal = ref(false);
const selectedAppointment = ref(null);

// Permessi lato frontend
const canCreateAppointment = computed(() => user.value?.roles?.includes('doctor'));
const canDeleteAppointment = computed(() => user.value?.roles?.includes('superadmin'));

// Funzioni per Card
const canEditAppointment = (appointment) => {
    return user.value?.doctor && appointment.doctor_id === user.value.doctor?.id;
}

const onEditAppointment = (appointment) => {
    selectedAppointment.value = appointment;
    showModal.value = true;
}

const refreshEntry = (appointment) => {
    medicalAppointment.value = appointment
}

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const d = String(date.getDate()).padStart(2, "0");
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const y = date.getFullYear();
    return `${d}/${m}/${y}`;
};

const getTime = (dateString) => {
    const date = new Date(dateString);
    const h = String(date.getHours());
    let m = String(date.getMinutes());
    if (m === 0) {
        m = "00"
    }
    else if (m < 10) {
        m = `0${m}`
    }

    return `${h}:${m}`;
}


</script>
<template>
    <div class="red-card p-4 mb-3">
        <div class="card-body">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5>
                    Appuntamento del {{ formatDate(appointment.start_time) }} {{ getTime(appointment.start_time) }}
                </h5>
                <div>

                    <button class="btn-negative"
                        v-if="canCreateAppointment && medicalAppointment.medical_entry === null"
                        @click="showModal = true">Aggiungi visita</button>
                </div>
            </div>
            <div>
                <p><em>{{ appointment.note }}</em></p>
            </div>
            <div v-if="medicalAppointment.medical_entry !== null">
                <!-- HEADER VISITA -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">
                            {{ medicalAppointment.medical_entry?.title }}
                        </h5>
                        <small class="text-white">
                            {{ medicalAppointment.medical_entry?.type }}
                            {{ medicalAppointment.medical_entry?.doctor?.user?.name }}
                            {{ medicalAppointment.medical_entry?.doctor?.user?.surname }}
                        </small>
                    </div>

                    <div class="text-white small">
                        Referto inserito il:{{ formatDate(medicalAppointment.medical_entry?.created_at) }} alle {{
                            getTime(medicalAppointment.medical_entry?.created_at) }}
                    </div>
                </div>

                <!-- CONTENUTO -->
                <div v-if="medicalAppointment.medical_entry?.content" class="mb-4">
                    <p class="mb-0 text-white">
                        {{ medicalAppointment.medical_entry?.content }}
                    </p>
                </div>

                <!-- PARAMETRI VITALI -->
                <div v-if="medicalAppointment.medical_entry?.vital_parameters" class="mb-4 text-white">
                    <h6 class="fw-bold">Parametri Vitali</h6>

                    <div class="row">
                        <div class="col-md-4" v-if="medicalAppointment.medical_entry?.vital_parameters.pressure">
                            <strong>Pressione:</strong>
                            {{ medicalAppointment.medical_entry?.vital_parameters.pressure }}
                        </div>

                        <div class="col-md-4" v-if="medicalAppointment.medical_entry?.vital_parameters.heart_rate">
                            <strong>Frequenza cardiaca:</strong>
                            {{ medicalAppointment.medical_entry?.vital_parameters.heart_rate }} bpm
                        </div>

                        <div class="col-md-4" v-if="medicalAppointment.medical_entry?.vital_parameters.temperature">
                            <strong>Temperatura:</strong>
                            {{ medicalAppointment.medical_entry?.vital_parameters.temperature }} °C
                        </div>

                        <div class="col-md-6 mt-2" v-if="medicalAppointment.medical_entry?.vital_parameters.weight">
                            <strong>Peso:</strong>
                            {{ medicalAppointment.medical_entry?.vital_parameters.weight }} kg
                        </div>

                        <div class="col-md-6 mt-2" v-if="medicalAppointment.medical_entry?.vital_parameters.height">
                            <strong>Altezza:</strong>
                            {{ medicalAppointment.medical_entry?.vital_parameters.height }} cm
                        </div>
                    </div>
                </div>
                <hr class="text-white">
                <!-- PRESCRIZIONI -->
                <div v-if="medicalAppointment.medical_entry?.prescriptions && medicalAppointment.medical_entry?.prescriptions.length"
                    class="mb-4">
                    <h6 class="fw-bold text-white">Prescrizioni</h6>

                    <div v-for="prescription in medicalAppointment.medical_entry?.prescriptions" :key="prescription.id"
                        class="border rounded p-3 mb-2 bg-white">
                        <div class="fw-semibold">
                            {{ prescription.drug_name }}
                        </div>

                        <div class="small text-muted">
                            {{ prescription.dosage }} •
                            {{ prescription.frequency }} •
                            {{ prescription.duration }}
                        </div>

                        <div v-if="prescription.notes" class="mt-2">
                            {{ prescription.notes }}
                        </div>
                    </div>
                </div>

                <!-- ALLEGATI -->
                <div
                    v-if="medicalAppointment.medical_entry?.attachments && medicalAppointment.medical_entry?.attachments.length">
                    <h6 class="fw-bold">Allegati</h6>

                    <ul class="list-unstyled mb-0">
                        <li v-for="attachment in medicalAppointment.medical_entry?.attachments" :key="attachment.id">
                            <a :href="attachment.url" target="_blank">
                                {{ attachment.name }}
                            </a>
                        </li>
                    </ul>
                </div>

            </div>


        </div>
        <CreateMedicalEntryModal v-if="showModal" :entry="selectedAppointment" :patient="patient"
            :appointment="appointment" @close="showModal = false" @saved="refreshEntry" />
    </div>
</template>
<style lang="scss" scoped>
@use '../../scss/app.scss';
@use '../../scss/_partials/variables' as *;

.red-card {
    background-color: $mainRed;

    h5 {
        color: #fff;
    }
}
</style>