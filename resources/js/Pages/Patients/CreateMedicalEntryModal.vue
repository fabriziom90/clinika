<script setup>
import { ref, defineEmits, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    entry: Object,
    patient: Object,
    appointment: Object
});

const emit = defineEmits(['saved', 'close'])

/* =========================
   STATO ENTRY PRINCIPALE
========================= */
const type = ref("visit");
const title = ref("");
const content = ref("");
const saving = ref(false);

/* =========================
   PARAMETRI VITALI (hasOne)
========================= */
const vitalParameters = ref({
    pressure: null,
    heart_rate: null,
    temperature: null,
    weight: null,
    height: null,
});

/* =========================
   PRESCRIZIONI (hasMany)
========================= */
const prescriptions = ref([
    {
        drug_name: "",
        dosage: "",
        frequency: "",
        duration: "",
        notes: "",
    },
]);

/* =========================
   RESET MODALE
========================= */
watch(() => props.show, (val) => {
    if (val) {
        type.value = props.entry?.type || "visit";
        title.value = props.entry?.title || "";
        content.value = props.entry?.content || "";
    }
});

/* =========================
   AGGIUNGI / RIMUOVI FARMACO
========================= */
const addPrescription = () => {
    prescriptions.value.push({
        drug_name: "",
        dosage: "",
        frequency: "",
        duration: "",
        notes: "",
    });
};

const removePrescription = (index) => {
    prescriptions.value.splice(index, 1);
};

/* =========================
   SALVATAGGIO
========================= */
const saveEntry = () => {
    saving.value = true;

    router.post(route("admin.medical-entries.store"), {
        patient_id: props.patient.id,
        medical_record_id: props.patient.medical_record.id,
        appointment_id: props.appointment.id,
        type: type.value,
        title: title.value,
        content: content.value,
        vital_parameters: vitalParameters.value,
        prescriptions: prescriptions.value.filter(p => p.drug_name.trim() !== ""),
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            // window.location.reload();

            emit('saved', page.props.appointmentEntry)
            emit("close");
        },
        onFinish: () => saving.value = false,
    });
};
</script>

<template>
    <div class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">
                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ entry ? "Modifica Entry Clinica" : "Nuova Entry Clinica" }}
                    </h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <!-- Tipo -->
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select v-model="type" class="form-select">
                            <option value="visit">Visita</option>
                            <option value="note">Nota clinica</option>
                            <option value="prescription">Prescrizione</option>
                            <option value="exam">Referto/Esame</option>
                            <option value="diagnosis">Diagnosi</option>
                        </select>
                    </div>

                    <!-- Titolo -->
                    <div class="mb-3">
                        <label class="form-label">Titolo</label>
                        <input v-model="title" type="text" class="form-control" />
                    </div>

                    <!-- Contenuto -->
                    <div class="mb-4">
                        <label class="form-label">Contenuto</label>
                        <textarea v-model="content" rows="5" class="form-control"></textarea>
                    </div>

                    <hr>

                    <!-- PARAMETRI VITALI -->
                    <h6 class="mb-3">Parametri Vitali (opzionale)</h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pressione</label>
                            <input v-model="vitalParameters.pressure" class="form-control" placeholder="120/80" />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Frequenza cardiaca</label>
                            <input v-model="vitalParameters.heart_rate" type="number" class="form-control" />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Temperatura (°C)</label>
                            <input v-model="vitalParameters.temperature" type="number" step="0.1"
                                class="form-control" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peso (kg)</label>
                            <input v-model="vitalParameters.weight" type="number" step="0.1" class="form-control" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Altezza (cm)</label>
                            <input v-model="vitalParameters.height" type="number" step="0.1" class="form-control" />
                        </div>
                    </div>

                    <hr>

                    <!-- PRESCRIZIONI -->
                    <h6 class="mb-3">Prescrizioni (opzionale)</h6>

                    <div v-for="(prescription, index) in prescriptions" :key="index" class="border rounded p-3 mb-3">
                        <div class="mb-2">
                            <label class="form-label">Farmaco</label>
                            <input v-model="prescription.drug_name" class="form-control" />
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Dosaggio</label>
                                <input v-model="prescription.dosage" class="form-control" />
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Frequenza</label>
                                <input v-model="prescription.frequency" class="form-control" />
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Durata</label>
                                <input v-model="prescription.duration" class="form-control" />
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Note</label>
                            <textarea v-model="prescription.notes" rows="2" class="form-control"></textarea>
                        </div>

                        <button class="btn btn-sm btn-outline-danger" @click="removePrescription(index)">
                            Rimuovi
                        </button>
                    </div>

                    <button class="btn btn-sm btn-outline-primary" @click="addPrescription">
                        + Aggiungi Farmaco
                    </button>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer">
                    <button class="secondary-button" @click="$emit('close')" :disabled="saving">
                        Annulla
                    </button>
                    <button class="main-button" @click="saveEntry" :disabled="saving">
                        {{ saving ? "Salvando..." : "Salva visita" }}
                    </button>
                </div>


            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;

.modal-bg {
    background-color: $mainBgTransparent;

    .modal-dialog {
        max-width: 800px;
    }

    .modal-header {
        background-color: $mainRed;
        color: #fff;
    }
}
</style>
