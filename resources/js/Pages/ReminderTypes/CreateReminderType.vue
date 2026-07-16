<script setup>
import { Link, Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';


const props = defineProps({
    reminderType: Object
})

const form = useForm({
    name: "",
    subject: "",
    message: "",
    sent_before_value: "",
    sent_before_unit: "",
    active: false
})

const save = () => {
    form.post(route('admin.reminder-types.store'), {
        preserveScroll: true
    })
}

</script>
<template>

    <Head title="Crea tipologia di promemoria" />
    <AuthenticatedLayout section="remindertypes">
        <div>
            <h2>Crea tipologia di promemoria</h2>
        </div>
        <div class="row gy-3">
            <div class="col-12 col-md-4">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" placeholder="Nome"
                    v-model="form.name">
                <div v-if="form.errors.name" class="text-danger small mt-1">
                    {{ form.errors.name }}
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Tempistica</label>
                <div class="d-flex">
                    <div class="me-2">
                        <input type="number" class="form-control"
                            :class="{ 'is-invalid': form.errors.sent_before_value }" placeholder="Tempo"
                            v-model="form.sent_before_value">
                        <div v-if="form.errors.sent_before_value" class="text-danger small mt-1">
                            {{ form.errors.sent_before_value }}
                        </div>
                    </div>
                    <div>
                        <select class="form-select" :class="{ 'is-invalid': form.errors.sent_before_unit }"
                            v-model="form.sent_before_unit">
                            <option value="">--Seleziona tempistica--</option>
                            <option value="hours">Ore</option>
                            <option value="days">Giorni</option>
                        </select>
                        <div v-if="form.errors.sent_before_unit" class="text-danger small mt-1">
                            {{ form.errors.sent_before_unit }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Attivo</label>
                <div class="form-check form-switch mb-3">
                    <input v-model="form.active" :checked="form.active" class="form-check-input custom-switch"
                        :class="{ 'is-invalid': form.errors.active }" type="checkbox" id="active">
                    <label class="form-check-label mt-1 ms-2" for="active">
                        Sistema attivo
                    </label>
                    <div v-if="form.errors.active" class="text-danger small mt-1">
                        {{ form.errors.active }}
                    </div>
                </div>
            </div>
            <div class="col-12">
                <h2>Testo promemoria</h2>
            </div>
            <div class="col-12">
                <label for="" class="form-label">Oggetto</label>
                <input type="text" class="form-control" :class="{ 'is-invalid': form.subject }"
                    placeholder="Oggetto (facoltativo)" v-model="form.subject">
                <div v-if="form.errors.subject" class="text-danger small mt-1">
                    {{ form.errors.subject }}
                </div>
            </div>
            <div class="col-12">
                <label for="" class="form-label">Testo promemoria</label>
                <div v-span>
                    <em>Nel testo inserire { { nome_cognome } }, { { data_appuntamento } } e { { orario_appuntamento } }
                        come segnaposti del nome e cognome del paziente, la data e l'orario dell'appuntamento</em>
                </div>
                <textarea class="form-control" :class="{ 'is-invalid': form.errors.message }"
                    v-model="form.message"></textarea>
                <div v-if="form.errors.message" class="text-danger small mt-1">
                    {{ form.errors.message }}
                </div>
            </div>
            <div class="col-12">
                <button class="main-button" type="submit" @click="save">Salva</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
