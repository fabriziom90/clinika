<script setup>
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ServiceModal from './ServiceModal.vue'
import { useToast } from 'vue-toast-notification'
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const $toast = useToast()

const props = defineProps({
    services: Array,
    specialty: Object
})

const form = useForm({
    name: props.specialty.name,
    service_ids: []
})

const selectedServices = ref(props.specialty.services ?? []);

const showServiceModal = ref(false)

const handleSubmitForm = () => {
    if (selectedServices.value.length === 0) {
        $toast.error('Seleziona una prestazione sanitaria')
        return
    }

    form.service_ids = selectedServices.value.map(service => service.id);
    form.put(route('admin.specialties.update', props.specialty.id));
}

const onServicesSaved = (newServices) => {
    newServices.forEach(s => props.services.push(s))
    
    showServiceModal.value = false
}
</script>


<template>
    <Head title="Crea specializzazione" />

    <AuthenticatedLayout section="specialties">
        <h2>Aggiungi specializzazione</h2>

        <form @submit.prevent="handleSubmitForm" class="mt-4">
            <div class="row gy-3">

                <div class="col-md-4">
                    <label class="form-label">Nome specializzazione</label>
                    <input class="form-control" v-model="form.name" placeholder="Nome" :class="{ 'is-invalid': form.errors.name }"/>
                    <span v-if="form.errors.name" class="text-danger">
                        {{form.errors.name}}
                    </span>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Prestazione sanitaria</label>
                    <Multiselect
                        v-model="selectedServices"
                        :options="services"
                        :multiple="true"
                        :searchable="true"
                        track-by="id"
                        label="name"
                        placeholder="Seleziona una o più prestazioni"
                        select-label="Premi invio per selezionare"
                        select-group-label="Premi invio per selezionare il gruppo"
                        selected-label="Selezionato"
                        deselect-label="Premi invio per rimuovere"
                        deselect-group-label="Premi invio per rimuovere il gruppo"
                        no-options="Nessuna prestazione disponibile"
                        no-result="Nessun risultato trovato"
                    />
                    <small class="text-muted d-block mt-1">
                        Non trovi la prestazione?
                        <a class="text-red" href="#" @click.prevent="showServiceModal = true">
                            Aggiungila cliccando qui
                        </a>
                    </small>
                </div>

                <div class="col-12">
                    <button class="main-button" type="submit">
                        Salva specializzazione
                    </button>
                </div>
            </div>
        </form>

        <ServiceModal
            v-if="showServiceModal"
            @close="showServiceModal = false"
            @saved="onServicesSaved"
        />
    </AuthenticatedLayout>
</template>

<style lang="scss">
    @use '../../../scss/app.scss';
    @use '../../../scss/_partials/variables' as *;

    .text-red{
        color: $mainRed;
    }

    .multiselect__tag, .multiselect__option--highlight, .multiselect__option--highlight::after{
        background-color: $mainRed !important;
    }

    
</style>