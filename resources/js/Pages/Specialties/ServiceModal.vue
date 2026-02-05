<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification'

const $toast = useToast()

const emit = defineEmits(['close', 'saved'])

const servicesForm = useForm({
    services: [
        {
            name: '',
            default_duration: '',
            default_price: '',
            active: 1,
        }
    ]
})

const addRow = () => {
    servicesForm.services.push({
        name: '',
        default_duration: '',
        default_price: '',
        active: 1,
    })
}

const removeRow = (index) => {
    servicesForm.services.splice(index, 1)
}

const save = () => {
    servicesForm.post(route('admin.services.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            const services = page.props.flash?.services ?? []

            emit('saved', services)
            servicesForm.reset()
        }
    })
}
</script>

<template>
    <div  class="modal fade show modal-bg" style="display: block">
        <div id="modal-add-service" class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Nuove prestazioni sanitarie</h5>
                    <button
                        type="button"
                        class="btn-close"
                        @click="$emit('close')"
                    ></button>
                </div>
                <div class="modal-body">
                    <div
                        v-for="(service, index) in servicesForm.services"
                        :key="index"
                        class="p-3 mb-3"
                    >
                        <div class="row gy-2">
                            <div class="col-md-4">
                                <input class="form-control" placeholder="Nome" v-model="service.name" />
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="number" placeholder="Durata" v-model="service.default_duration" />
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="number" placeholder="Prezzo" v-model="service.default_price" />
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" v-model="service.active">
                                    <option :value="1">Attiva</option>
                                    <option :value="0">Disattiva</option>
                                </select>
                            </div>
                        </div>

                        <button
                            v-if="servicesForm.services.length > 1"
                            class="btn btn-sm btn-danger mt-2"
                            @click="removeRow(index)"
                            type="button"
                        >
                            Rimuovi
                        </button>
                    </div>

                    <button class="secondary-button mb-3" type="button" @click="addRow">
                        + Aggiungi prestazione
                    </button>

                    <div class="d-flex justify-content-end gap-2">
                        <button class="secondary-button" @click="$emit('close')" type="button">
                            Annulla
                        </button>
                        <button class="main-button" @click="save" type="button">
                            Salva prestazioni
                        </button>
                    </div>
                </div>
        
            </div>
        </div>
    </div>
</template>

<style lang="css" scoped>
    #modal-add-service{
        max-width: 800px;
    }
</style>
