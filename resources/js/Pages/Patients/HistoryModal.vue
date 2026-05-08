<script setup>
import { ref, defineEmits, onMounted, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    versions: Array
});

const types = ref([
    { name: 'visit', label: 'Visita' },
    { name: 'note', label: 'Nota' },
    { name: 'prescription', label: 'Prescrizione' },
    { name: 'exam', label: 'Esame' },
    { name: 'diagnosi', label: 'Diagnosi' }
])

const emit = defineEmits(['close'])

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

const formatType = (type) => {
    const found = types.value.find(item => item.name === type);
    return found ? found.label : type;
}

</script>

<template>
    <div class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Storico referti</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>

                <div class="modal-body">
                    <div class="p-4 mb-3 border-red" v-for="version in versions" :key="version.id"
                        :class="version.is_voided ? 'bg-red-opacity' : ''">
                        <h5>{{ version.title }} - {{ formatType(version.type) }} - {{ formatDate(version.created_at) }}
                            {{ getTime(version.created_at) }}
                        </h5>
                        <p>{{ version.content }}</p>
                        <div class="row">
                            <div class="col-12">
                                <h6>Parametri</h6>
                            </div>
                            <div class="col-2">
                                <label for=""><strong>Pressione</strong></label>
                                <p>{{ version.vital_parameters.pressure }}</p>
                            </div>
                            <div class="col-4">
                                <label for=""><strong>Frequenza Cardiaca</strong></label>
                                <p>{{ version.vital_parameters.heart_rate }}</p>
                            </div>
                            <div class="col-2">
                                <label for=""><strong>Temperatura</strong></label>
                                <p>{{ version.vital_parameters.temperature }}</p>
                            </div>
                            <div class="col-2">
                                <label for=""><strong>Peso</strong></label>
                                <p>{{ version.vital_parameters.weight }}</p>
                            </div>
                            <div class="col-2">
                                <label for=""><strong>Altezza</strong></label>
                                <p>{{ version.vital_parameters.height }}</p>
                            </div>
                        </div>
                        <div class="row" v-for="prescription in version.prescriptions"
                            :key="`prescription-${prescription.id}`">
                            <div class="col-2">
                                <label for=""><strong>Medicinale</strong></label>
                                <p>{{ prescription.drug_name }}</p>
                            </div>
                            <div class="col-2">
                                <label for=""><strong>Dose</strong></label>
                                <p>{{ prescription.dosage }}</p>
                            </div>
                            <div class="col-2">
                                <label for=""><strong>Frequenza</strong></label>
                                <p>{{ prescription.frequency }}</p>
                            </div>
                            <div class="col-2">
                                <label for=""><strong>Durata</strong></label>
                                <p>{{ prescription.duration }}</p>
                            </div>
                            <div class="col-12">
                                <label for=""><strong>Note</strong></label>
                                <p>{{ prescription.note }}</p>
                            </div>
                        </div>
                    </div>
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

    .bg-red-opacity {
        background-color: $mainRedOpacity;

    }
}
</style>
