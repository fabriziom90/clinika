<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    patient: Object,
    history: Object
})

const emit = defineEmits(['close', 'saved'])

const form = useForm({
    patient_id: props.patient.id,
    change_reason: '',
    allergies: '',
    chronic_diseases: '',
    current_therapies: '',
    surgical_history: '',
    family_history: '',
    lifestyle: '',
    vaccinations: '',
    notes: '',
})

watch(
    () => props.show,
    (isOpen) => {
        if (!isOpen) return;
        if (props.history) {
            form.change_reason = '';
            form.allergies = props.history.allergies ?? '';
            form.chronic_diseases = props.history.chronic_diseases ?? '';
            form.current_therapies = props.history.current_therapies ?? '';
            form.surgical_history = props.history.surgical_history ?? '';
            form.family_history = props.history.family_history ?? '';
            form.lifestyle = props.history.lifestyle ?? '';
            form.vaccinations = props.history.vaccinations ?? '';
            form.notes = props.history.notes ?? '';
        } else {
            form.reset();
            form.patient_id = props.patient.id;
        }
    }
)

const saveHistory = () => {
    form.post(route('admin.patient-health-history.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        }
    })
}

</script>
<template lang="">
    <div v-if="props.show" class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiorna anamnesi</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Motivo della modifica</label>
                        <textarea
                            v-model="form.change_reason"
                            class="form-control"
                            rows="2"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Allergie</label>
                        <textarea
                            v-model="form.allergies"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Patologie croniche</label>
                        <textarea
                            v-model="form.chronic_diseases"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Terapie in corso</label>
                        <textarea
                            v-model="form.current_therapies"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Interventi chirurgici</label>
                        <textarea
                            v-model="form.surgical_history"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Anamnesi familiare</label>
                        <textarea
                            v-model="form.family_history"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stile di vita</label>
                        <textarea
                            v-model="form.lifestyle"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vaccinazioni</label>
                        <textarea
                            v-model="form.vaccinations"
                            class="form-control"
                            rows="3"
                        ></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Note</label>
                        <textarea
                            v-model="form.notes"
                            class="form-control"
                            rows="4"
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="secondary-button" @click="$emit('close')">
                        Annulla
                    </button>
                    <button class="main-button" @click="saveHistory">
                        Salva
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use "../../scss/app.scss";
@use "../../scss/_partials/variables" as *;
</style>
