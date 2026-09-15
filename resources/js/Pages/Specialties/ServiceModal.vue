<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    service: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close', 'saved'])

const isEdit = !!props.service

const createForm = useForm({
    services: [
        {
            name: '',
            default_duration: '',
            default_price: '',
            preparation_instructions: '',
            active: 1,
        }
    ]
})

const editForm = useForm({
    name: props.service?.name ?? '',
    default_duration: props.service?.default_duration ?? '',
    default_price: props.service?.default_price ?? '',
    preparation_instructions: props.service?.preparation_instructions ?? '',
    active: props.service?.active ?? 1,
})

const addRow = () => {
    createForm.services.push({
        name: '',
        default_duration: '',
        default_price: '',
        preparation_instructions: '',
        active: 1,
    })
}

const removeRow = (index) => {
    createForm.services.splice(index, 1)
}

const save = () => {
    if (isEdit) {
        editForm.put(
            route('admin.services.update', props.service.id),
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    const service = page.props.flash?.service ?? null

                    emit('saved', service)
                }
            }
        )

        return
    }

    createForm.post(route('admin.services.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved')

        }
    })
}
</script>

<template>
    <div class="modal fade show modal-bg" style="display: block">
        <div id="modal-add-service" class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>
                        {{ isEdit ? 'Modifica prestazione sanitaria' : 'Nuove prestazioni sanitarie' }}
                    </h5>

                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>

                <div class="modal-body">

                    <!-- CREAZIONE -->
                    <template v-if="!isEdit">
                        <div v-for="(service, index) in createForm.services" :key="index" class="p-3 mb-3">
                            <div class="row gy-2">

                                <div class="col-md-4">
                                    <input class="form-control" placeholder="Nome" v-model="service.name" />
                                </div>

                                <div class="col-md-3">
                                    <input class="form-control" type="number" placeholder="Durata"
                                        v-model="service.default_duration" />
                                </div>

                                <div class="col-md-3">
                                    <input class="form-control" type="number" placeholder="Prezzo"
                                        v-model="service.default_price" />
                                </div>

                                <div class="col-12">
                                    <label class="form-label">
                                        Istruzioni per il paziente
                                    </label>

                                    <textarea class="form-control" rows="4"
                                        placeholder="Inserisci eventuali istruzioni o indicazioni per il paziente..."
                                        v-model="service.preparation_instructions"></textarea>

                                    <small class="text-muted">
                                        Queste istruzioni potranno essere utilizzate nei promemoria
                                        relativi alla prestazione.
                                    </small>
                                </div>

                                <div class="col-md-2">
                                    <select class="form-select" v-model="service.active">
                                        <option :value="1">Attiva</option>
                                        <option :value="0">Disattiva</option>
                                    </select>
                                </div>

                            </div>

                            <button v-if="createForm.services.length > 1" class="btn btn-sm btn-danger mt-2"
                                @click="removeRow(index)" type="button">
                                Rimuovi
                            </button>
                        </div>

                        <button class="secondary-button mb-3" type="button" @click="addRow">
                            + Aggiungi prestazione
                        </button>
                    </template>

                    <!-- MODIFICA -->
                    <template v-else>
                        <div class="p-3 mb-3">
                            <div class="row gy-2">

                                <div class="col-md-4">
                                    <label class="form-label">
                                        Nome
                                    </label>

                                    <input class="form-control" placeholder="Nome" v-model="editForm.name" />
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Durata
                                    </label>

                                    <input class="form-control" type="number" placeholder="Durata"
                                        v-model="editForm.default_duration" />
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Prezzo
                                    </label>

                                    <input class="form-control" type="number" placeholder="Prezzo"
                                        v-model="editForm.default_price" />
                                </div>

                                <div class="col-12">
                                    <label class="form-label">
                                        Istruzioni per il paziente
                                    </label>

                                    <textarea class="form-control" rows="4"
                                        placeholder="Inserisci eventuali istruzioni o indicazioni per il paziente..."
                                        v-model="editForm.preparation_instructions"></textarea>

                                    <small class="text-muted">
                                        Queste istruzioni potranno essere utilizzate nei promemoria
                                        relativi alla prestazione.
                                    </small>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">
                                        Stato
                                    </label>

                                    <select class="form-select" v-model="editForm.active">
                                        <option :value="1">Attiva</option>
                                        <option :value="0">Disattiva</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </template>

                    <div class="d-flex justify-content-end gap-2">

                        <button class="secondary-button" @click="$emit('close')" type="button">
                            Annulla
                        </button>

                        <button class="main-button" @click="save" type="button" v-loading
                            :data-loading-text="isEdit ? 'Salvataggio in corso' : 'Salvataggio in corso'">
                            {{ isEdit ? 'Salva modifiche' : 'Salva prestazioni' }}
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style lang="css" scoped>
#modal-add-service {
    max-width: 800px;
}
</style>
