<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    const props = defineProps({
        consentType: Object
    })

    const getLabel = (string) => {
        const labels = {
            "paper": "Cartaceo",
            "upload": "Upload",
            "electronic_signature": "Firma elettronica"
        }

        return labels[string];
    }
</script>
<template lang="">
    <Head title="Dettaglio tipologia consenso" />
    <AuthenticatedLayout section="consenttypes">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Dettaglio tipologia consenso</h2>
                    <Link class="main-button" :href="route('admin.consent-types.edit', consentType.id)" >Modifica Tipologia consenso</Link >
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 col-md-3">
                <label for="" class="form-label"><strong>Nome</strong></label>
                <div>
                    {{ consentType.name}}
                </div>
            </div>
            <div class="col-12 col-md-3">
                <label for="" class="form-label"><strong>Codice</strong></label>
                <div>{{ consentType.code}}</div>
            </div>
            <div class="col-12 col-md-3">
                <label for="" class="form-label"><strong>Metodo acquisizione</strong></label>
                <div>{{ getLabel(consentType.acquisition_method)}}</div>
            </div>
            <div class="col-12 col-md-3">
                <div class="d-flex">
                    <div class="me-4">
                        <label for="" class="form-label"><strong>Attivo</strong></label>
                        <div class="round" :class="consentType.is_active ? 'bg-main-green' : 'bg-main-red'"></div>
                    </div>
                    <div>
                        <label for="" class="form-label"><strong>Obbligatorio</strong></label>
                        <div class="round ms-4" :class="consentType.is_required ? 'bg-main-green' : 'bg-main-red'"></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <labels class="form-label"><strong>Descrizione</strong></labels>
                <div>{{ consentType.description }}</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use '../../../scss/app.scss' as *;
@use '../../../scss/_partials/variables' as *;

.round{
    width: 50px;
    height: 50px;
    border-radius: 50%;
}


</style>
