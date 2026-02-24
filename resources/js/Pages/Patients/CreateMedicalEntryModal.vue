<script setup>
import { ref, defineEmits, onMounted, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    entry: Object,
    patient: Object,
    appointment: Object
});

const emit = defineEmits(['saved', 'close'])
const isEdit = computed(() => !!props.entry?.id)
/* =========================
   STATO ENTRY PRINCIPALE
========================= */
const type = ref("visit");
const title = ref("");
const content = ref("");
const saving = ref(false);
const voidVersion = ref(false);
const voidReason = ref("");


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
onMounted(() => {
    if (!props.entry) return;
    const latest = props.entry.medical_entry.latest_version;

    type.value = latest?.type || "visit";
    title.value = latest?.title || "";
    content.value = latest?.content || "";

    vitalParameters.value = {
        pressure: latest?.vital_parameters?.pressure ?? null,
        heart_rate: latest?.vital_parameters?.heart_rate ?? null,
        temperature: latest?.vital_parameters?.temperature ?? null,
        weight: latest?.vital_parameters?.weight ?? null,
        height: latest?.vital_parameters?.height ?? null,
    };

    prescriptions.value = latest?.prescriptions?.length
        ? latest.prescriptions.map(p => ({
            drug_name: p.drug_name,
            dosage: p.dosage,
            frequency: p.frequency,
            duration: p.duration,
            notes: p.notes,
        }))
        : [{
            drug_name: "",
            dosage: "",
            frequency: "",
            duration: "",
            notes: "",
        }];


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

    const payload = {
        patient_id: props.patient.id,
        medical_record_id: props.patient.medical_record.id,
        appointment_id: props.appointment.id,
        type: type.value,
        title: title.value,
        content: content.value,
        vital_parameters: vitalParameters.value,
        prescriptions: prescriptions.value.filter(p => p.drug_name.trim() !== ""),
        is_voided: voidVersion.value ? true : undefined,
        void_reason: voidVersion.value ? voidReason.value : undefined,
    };

    if (isEdit.value) {

        router.put(
            route("admin.medical-entries.update", props.entry.medical_entry.id),
            payload,
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    emit('saved', page.props.appointmentEntry);
                    emit("close");
                },
                onFinish: () => saving.value = false,
            }
        );

    } else {

        router.post(
            route("admin.medical-entries.store"),
            payload,
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    emit('saved', page.props.appointmentEntry);
                    emit("close");
                },
                onFinish: () => saving.value = false,
            }
        );
    }
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
                    <div v-if="isEdit" class="mt-4 p-3 border rounded bg-warning bg-opacity-10">
                        <label class="form-check-label mb-1">
                            <input type="checkbox" class="form-check-input me-2" v-model="voidVersion">
                            Annulla questa versione
                        </label>
                        <textarea v-if="voidVersion" v-model="voidReason" placeholder="Motivo annullamento..." rows="2"
                            class="form-control mt-2"></textarea>
                    </div>
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
