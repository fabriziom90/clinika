<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    histories: Array
})

const emit = defineEmits(['close']);

const sortDirection = ref('desc');
const mode = ref("list");
const selectedHistory = ref(null);
const previousHistory = ref(null);

const toggleSort = () => {
    sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc';
}

const sortedHistories = computed(() => {
    if (!props.histories) return [];

    return [...props.histories].sort((a, b) => {
        const va = parseInt(a.version);
        const vb = parseInt(b.version);

        return sortDirection.value === 'asc' ? va - vb : vb - va;
    })
})

const openCompare = (history) => {
    const index = sortedHistories.value.findIndex(h => h.id === history.id)

    if (index === sortedHistories.value.length - 1)
        previousHistory.value = null;
    else
        previousHistory.value = sortedHistories.value[index + 1];

    selectedHistory.value = history;
    mode.value = "compare";
}

const backToList = () => {
    selectedHistory.value = null;
    previousHistory.value = null;
    mode.value = "list";
}

const fields = [
    {
        key: "allergies",
        label: "Allergie"
    },
    {
        key: "chronic_diseases",
        label: "Patologie croniche"
    },
    {
        key: "current_therapies",
        label: "Terapie"
    },
    {
        key: "surgical_history",
        label: "Chirurgia"
    },
    {
        key: "family_history",
        label: "Famiglia"
    },
    {
        key: "lifestyle",
        label: "Stile vita"
    },
    {
        key: "vaccinations",
        label: "Vaccini"
    },
    {
        key: "notes",
        label: "Note"
    }
]

const hasChanged = (field) => {
    if (!previousHistory.value || !selectedHistory.value) return false;

    return previousHistory.value[field.key] !== selectedHistory.value[field.key];
}

</script>
<template lang="">
    <div v-if="show" class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Storico anamnesi
                        <button class="btn btn-white me-2" @click="toggleSort">
                            <i class="text-white fas" :class="sortDirection === 'asc' ? 'fa-caret-up' : 'fa-caret-down'"></i>
                        </button>
                    </h5>
                    <button class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <!-- LISTA STORICO -->
                    <div v-if="mode === 'list'">

                        <div v-if="!histories.length">
                            Nessuna anamnesi presente
                        </div>
                        <div v-else class="timeline">
                            <div
                                v-for="history in sortedHistories"
                                :key="history.id"
                                class="history-item mb-4 p-3 border rounded"
                            >
                                <div class="d-flex justify-content-between">
                                    <strong>
                                        Versione {{ history.version }}
                                    </strong>
                                    <small>
                                        {{ new Date(history.created_at).toLocaleString() }}
                                    </small>
                                </div>
                                <p class="mt-2">
                                    <strong>Modificato da:</strong>
                                    {{ history.author?.name }} {{ history.author?.surname }}
                                </p>
                                <p v-if="history.change_reason">
                                    <strong>Motivo:</strong>
                                    {{ history.change_reason }}
                                </p>
                                <div>
                                    <p><strong>Allergie:</strong> {{ history.allergies ?? 'Nessuna'}}</p>
                                    <p><strong>Patologie croniche:</strong> {{ history.chronic_diseases ?? 'Nessuna' }}</p>
                                    <p><strong>Terapie:</strong> {{ history.current_therapies ?? 'Nessuna'}}</p>
                                    <p><strong>Chirurgia:</strong> {{ history.surgical_history ?? 'Nessuna'}}</p>
                                    <p><strong>Famiglia:</strong> {{ history.family_history ?? 'Nessuna'}}</p>
                                    <p><strong>Stile vita:</strong> {{ history.lifestyle ?? 'Nessuna'}}</p>
                                    <p><strong>Vaccini:</strong> {{ history.vaccinations ?? 'Nessuna'}}</p>
                                    <p><strong>Note:</strong> {{ history.notes ?? 'Nessuna'}}</p>
                                </div>
                                <button
                                    class="btn btn-danger"
                                    @click="openCompare(history)"
                                >
                                    Confronta con precedente
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- CONFRONTO -->
                    <div v-else>
                        <button
                            class="btn btn-secondary mb-3"
                            @click="backToList"
                        >
                            Torna allo storico
                        </button>
                        <h5>
                            Confronto versione
                            {{ previousHistory?.version ?? 'nessuna' }}
                            →
                            {{ selectedHistory.version }}
                        </h5>
                        <div v-if="!previousHistory">
                            Questa è la prima versione dell'anamnesi.
                        </div>
                        <table
                            v-else
                            class="table"
                        >
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Versione precedente</th>
                                    <th>Versione nuova</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="field in fields"
                                    :key="field.key"
                                    :class="{
                                        'table-warning': hasChanged(field)
                                    }"
                                >
                                    <td>
                                        <strong>
                                            {{ field.label }}
                                        </strong>
                                    </td>
                                    <td>
                                        {{ previousHistory[field.key] ?? 'Nessuna' }}
                                    </td>
                                    <td>
                                        {{ selectedHistory[field.key] ?? 'Nessuna' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use "../../scss/app.scss";
@use "../../scss/_partials/variables" as *;
</style>
