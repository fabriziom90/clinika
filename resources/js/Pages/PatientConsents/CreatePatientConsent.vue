<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    patient: Object,
    consentTypes: Array,
});

const $toast = useToast();

const acquisitionMethodLabels = {
    paper: "Cartaceo",
    upload: "Upload documento",
    electronic_signature: "Firma elettronica",
};

const form = useForm({
    consents: props.consentTypes.map((consentType) => ({
        consent_type_id: consentType.id,
        consent_version_id: consentType.versions?.[0]?.id ?? null,
        status: "pending",
        acquisition_method: consentType.acquisition_method ?? null,
        document: null,
    })),
});
const getConsentForm = (consentTypeId) => {
    return form.consents.find(
        (consent) => consent.consent_type_id === consentTypeId,
    );
};
const handleFileChange = (consentTypeId, event) => {
    const consent = getConsentForm(consentTypeId);
    if (!consent) {
        return;
    }
    consent.document = event.target.files?.[0] ?? null;
};
const submit = () => {
    form.post(route("admin.patient.consents.store", props.patient.id), {
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

    <Head title="Inserisci consensi paziente" />
    <AuthenticatedLayout section="patients">
        <div class="row">
            <div class="col-12">
                <h2>
                    Inserisci consensi paziente
                    <strong>{{ patient.name }} {{ patient.surname }}</strong>
                </h2>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <form @submit.prevent="submit">
                    <div v-if="!consentTypes.length" class="alert alert-warning">
                        Non sono presenti tipologie di consenso attive.
                    </div>
                    <div v-for="consentType in consentTypes" :key="consentType.id" class="card mb-4">
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Informazioni consenso -->
                                <div class="col-12">
                                    <h4 class="mb-2">{{ consentType.name }}</h4>
                                    <p v-if="consentType.description" class="text-muted mb-2">
                                        {{ consentType.description }}
                                    </p>
                                    <div v-if="!consentType.versions?.length" class="alert alert-danger mb-0">
                                        Nessuna versione attiva disponibile per
                                        questo consenso.
                                    </div>
                                </div>
                                <template v-if="getConsentForm(consentType.id) && consentType.versions?.length">
                                    <!-- Stato -->
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">
                                            Stato del consenso
                                        </label>
                                        <select v-model="getConsentForm(consentType.id).status" class="form-select">
                                            <option value="pending">
                                                In attesa
                                            </option>
                                            <option value="accepted">
                                                Accettato
                                            </option>
                                            <option value="rejected">
                                                Rifiutato
                                            </option>
                                            <option value="revoked">
                                                Revocato
                                            </option>
                                        </select>
                                    </div>
                                    <!-- Metodo acquisizione -->
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">
                                            Metodo di acquisizione
                                        </label>
                                        <div class="form-control bg-light">
                                            {{
                                                acquisitionMethodLabels[
                                                getConsentForm(
                                                    consentType.id,
                                                ).acquisition_method
                                                ] ?? "Non specificato"
                                            }}
                                        </div>
                                    </div>
                                    <!-- Versione -->
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">
                                            Versione
                                        </label>
                                        <div class="form-control bg-light">
                                            Versione
                                            {{
                                                consentType.versions[0].version
                                            }}
                                        </div>
                                    </div>
                                    <!-- Upload -->
                                    <div v-if="
                                        getConsentForm(consentType.id)
                                            .acquisition_method === 'upload'
                                    " class="col-12">
                                        <label class="form-label">
                                            Documento firmato
                                        </label>
                                        <input type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" @change="
                                            handleFileChange(
                                                consentType.id,
                                                $event,
                                            )
                                            " />
                                        <small class="text-muted">
                                            Carica il documento firmato relativo
                                            alla versione del consenso.
                                        </small>
                                    </div>
                                    <!-- Firma elettronica -->
                                    <div v-if="
                                        getConsentForm(consentType.id)
                                            .acquisition_method ===
                                        'electronic_signature'
                                    " class="col-12">
                                        <div class="alert alert-info mb-0">
                                            La firma elettronica sarà
                                            disponibile prossimamente.
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="main-button" :disabled="form.processing">
                            <span v-if="form.processing">
                                Salvataggio in corso...
                                <i class="fa-solid fa-spinner fa-spin ms-2"></i>
                            </span>
                            <span v-else> Salva consensi </span>
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
