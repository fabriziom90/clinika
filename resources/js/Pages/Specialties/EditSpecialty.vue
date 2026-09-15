<script setup>
import { ref, watch } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ServiceModal from './ServiceModal.vue'
import Table from '@/Components/Table.vue'
import { useToast } from 'vue-toast-notification'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

const $toast = useToast()

const props = defineProps({
    services: Array,
    specialty: Object
})

const form = useForm({
    name: props.specialty.name,
    service_ids: props.specialty.services?.map(service => service.id) ?? []
})

const servicesList = ref([...props.services])

watch(
    () => props.services,
    (services) => {
        servicesList.value = [...services]
    }
)

const selectedServices = ref([...props.specialty.services ?? []])

const showServiceModal = ref(false)
const selectedService = ref(null)

const serviceColumns = {
    name: 'Prestazione',
    default_duration: 'Durata',
    default_price: 'Prezzo',
    active: 'Stato'
}

const handleSubmitForm = () => {
    if (selectedServices.value.length === 0) {
        $toast.error('Seleziona una prestazione sanitaria')
        return
    }

    form.service_ids = selectedServices.value.map(service => service.id)

    form.put(route('admin.specialties.update', props.specialty.id))
}

const openCreateServiceModal = () => {
    selectedService.value = null
    showServiceModal.value = true
}

const editService = (service) => {
    selectedService.value = service
    showServiceModal.value = true
}

const closeServiceModal = () => {
    showServiceModal.value = false
    selectedService.value = null
}

const onServicesSaved = (savedServices) => {

    closeServiceModal()
}
</script>

<template>

    <Head title="Modifica specializzazione" />

    <AuthenticatedLayout section="specialties">
        <h2>Modifica specializzazione</h2>

        <form @submit.prevent="handleSubmitForm" class="mt-4">
            <div class="row gy-3">

                <div class="col-md-4">
                    <label class="form-label">Nome specializzazione</label>

                    <input class="form-control" v-model="form.name" placeholder="Nome"
                        :class="{ 'is-invalid': form.errors.name }" />

                    <span v-if="form.errors.name" class="text-danger">
                        {{ form.errors.name }}
                    </span>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Prestazione sanitaria</label>

                    <Multiselect v-model="selectedServices" :options="servicesList" :multiple="true" :searchable="true"
                        track-by="id" label="name" placeholder="Seleziona una o più prestazioni"
                        select-label="Premi invio per selezionare"
                        select-group-label="Premi invio per selezionare il gruppo" selected-label="Selezionato"
                        deselect-label="Premi invio per rimuovere"
                        deselect-group-label="Premi invio per rimuovere il gruppo"
                        no-options="Nessuna prestazione disponibile" no-result="Nessun risultato trovato" />

                    <span v-if="form.errors.service_ids" class="text-danger">
                        {{ form.errors.service_ids }}
                    </span>
                </div>

                <div class="col-12">
                    <button class="main-button" type="submit" v-loading data-loading-text="Salvataggio in corso">
                        Salva modifiche
                    </button>
                </div>

            </div>
        </form>

        <div class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Prestazioni sanitarie</h3>

                <button type="button" class="main-button" @click="openCreateServiceModal">
                    <i class="fas fa-plus me-1"></i>
                    Nuova prestazione
                </button>
            </div>

            <Table :items="servicesList" :columns="serviceColumns" base-route="admin.services" custom-edit
                @edit="editService" />
        </div>

        <ServiceModal v-if="showServiceModal" :service="selectedService" @close="closeServiceModal"
            @saved="onServicesSaved" />
    </AuthenticatedLayout>
</template>

<style lang="scss">
@use '../../../scss/app.scss';
@use '../../../scss/_partials/variables' as *;

.text-red {
    color: $mainRed;
}

.multiselect__tag,
.multiselect__option--highlight,
.multiselect__option--highlight::after {
    background-color: $mainRed !important;
}
</style>
