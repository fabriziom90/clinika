<script setup>
    import { Head, useForm } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

    const props = defineProps({
        consentType: {
            type: Object,
            required: true
        }
    })

    const form = useForm({
        content: "",
        is_active: true
    })

    const submit = () => {
        form.post(route("admin.consent-types.consent-versions.store", props.consentType.id));
    }
</script>
<template>
    <Head title="Aggiungi versione"/>
    <AuthenticatedLayout section="consentversions">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Aggiungi versione - {{ consentType.name }}</h2>
                    <button @click="WindowScrollController.history.back" class="main-button">Torna indietro</button>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <form @submit.prevent="submit">
                    <div class="row gy-3">
                        <div class="col-12">
                            <label for="" class="form-label">Contenuto del consenso</label>
                            <textarea name="" id="" class="form-control" placeholder="Contenuto del conseso" :class="{'is-invalid': form.errors.content }" rows="15" v-model="form.content"></textarea>
                            <div v-if="form.errors.content" class="text-danger mt-1">
                                {{  form.errors.content }}
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input id="is_active" v-model="form.is_active" class="form-check-input custom-switch" type="checkbox" />
                                <label class="form-check-label mt-1 ms-2" for="is_active">
                                    Versione attiva
                                </label>
                            </div>
                            <div v-if="form.errors.is_active" class="text-danger mt-1">
                                {{ form.errors.is_active }}
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="main-button" :disabled="form.processing">
                                Salva versione
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss">

</style>
