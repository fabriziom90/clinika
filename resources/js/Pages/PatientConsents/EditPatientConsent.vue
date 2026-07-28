<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    patient: Object,
    patientConsent: Object
});

const $toast = useToast();

const acquisitionMethodLabels = {
    paper: "Cartaceo",
    upload: "Upload documento",
    electronic_signature: "Firma elettronica",
};

const form = useForm({
    consent_type_id: props.patientConsent.consent_type_id,
    consent_version_id: props.patientConsent.consent_version_id,
    status: props.patientConsent.status,
    acquisition_method: props.patientConsent.acquisition_method,
    document: null,
});


const handleFileChange = (event) => {
    form.document = event.target.files?.[0] ?? null;
};

const submit = () => {
    
    form.transform((data) => ({
            ...data,
            _method: "PUT",
        }))
        .post(route("admin.patient.consents.update", {patient: props.patient.id, consent: props.patientConsent.id}), {
        forceFormData: true,
        onError: (errors) => {
            const messages = Object.values(errors).flat();
            if (messages.length) {
                $toast.error(messages.join("\n"));
            }
        },
    });
};
</script>

<template>

    <Head title="Modifica consensi paziente" />
    <AuthenticatedLayout section="patients">
        <div class="row">
            <div class="col-12">
                <h2>
                    Modifica consensi paziente
                    <strong>{{ patient.name }} {{ patient.surname }}</strong>
                </h2>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <form @submit.prevent="submit">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Informazioni consenso -->
                                <div class="col-12">
                                    <h4 class="mb-2">
                                        {{ patientConsent.consent_type.name }}
                                    </h4>

                                    <p v-if="patientConsent.consent_type.description" class="text-muted mb-2">
                                        {{ patientConsent.consent_type.description }}
                                    </p>
                                </div>

                                <!-- Stato -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Stato del consenso</label>

                                    <select v-model="form.status" class="form-select" >
                                        <option value="pending">In attesa</option>
                                        <option value="accepted">Accettato</option>
                                        <option value="rejected">Rifiutato</option>
                                        <option value="revoked">Revocato</option>
                                    </select>
                                </div>

                                <!-- Metodo acquisizione -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Metodo di acquisizione</label>
                                    <div class="form-control bg-light">
                                        {{ acquisitionMethodLabels[form.acquisition_method] ?? "Non specificato"}}
                                    </div>
                                </div>

                                <!-- Versione -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Versione</label>
                                    <div class="form-control bg-light">
                                        Versione {{ patientConsent.consent_version.version }}
                                    </div>
                                </div>

                                <!-- Upload documento -->
                                <div v-if="form.acquisition_method === 'upload'" class="col-12">
                                    <label class="form-label">Documento firmato</label>
                                    <input type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileChange"/>

                                    <small class="text-muted">Carica un nuovo documento firmato relativo alla versione del consenso.</small>
                                </div>

                                <!-- Firma elettronica -->
                                <div v-if=" form.acquisition_method === 'electronic_signature'" class="col-12">
                                    <div class="alert alert-info mb-0">
                                        La firma elettronica sarà disponibile prossimamente.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="main-button" :disabled="form.processing">
                            <span v-if="form.processing">
                                Salvataggio in corso...
                                <i class="fa-solid fa-spinner fa-spin ms-2"></i>
                            </span>

                            <span v-else>Salva consenso</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped>
@use "../../../scss/app.scss" as *;
</style>
