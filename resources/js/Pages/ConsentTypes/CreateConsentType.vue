<script setup>
    import { Head, useForm } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

    const form = useForm({
        name: "",
        description: "",
        acquisition_method: "paper",
        is_required: true,
        is_active: true
    })

    const submit = () => {
        form.post(route("admin.consent-types.store"), {
            onError: (errors) => {
                console.log(errors);
            }
        });
    }
</script>
<template lang="">
    <Head title="Aggiungi modulo consenso"/>
    <AuthenticatedLayout section="consenttypes">
        <div class="row">
            <div class="col-12">
                <h2>Aggiungi tipologia di consenso</h2>
            </div>
            <form @submit.prevent="submit">
                <div class="row gy-3">
                    <div class="col-12 col-md-3">
                        <label for="" class="form-label">Nome</label>
                        <input type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" placeholder="Nome" v-model="form.name">
                        <div
                            v-if="form.errors.name"
                            class="text-danger mt-1"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="" class="form-label">Modalità acquisizione</label>
                        <select
                            v-model="form.acquisition_method"
                            class="form-select"
                        >
                            <option value="paper">Cartaceo</option>
                            <option value="upload">Upload documento firmato</option>
                            <option value="electronic_signature">Firma elettronica</option>
                        </select>

                        <div v-if="form.errors.acquisition_method" class="text-danger mt-1">
                            {{ form.errors.acquisition_method }}
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-check form-switch mt-4">
                            <input
                                id="is_required"
                                v-model="form.is_required"
                                class="form-check-input custom-switch"
                                type="checkbox"
                            >

                            <label
                                class="form-check-label mt-1 ms-2"
                                for="is_required"
                            >
                                Consenso obbligatorio
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-check form-switch mt-4">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                class="form-check-input custom-switch"
                                type="checkbox"
                            >

                            <label
                                class="form-check-label mt-1 ms-2"
                                for="is_active"
                            >
                                Attivo
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="" class="form-label">Descrizione</label>
                        <textarea name="" id="" class="form-control" :class="{'is-invalid': form.errors.description}" placeholder="Descrizione" v-model="form.description"></textarea>
                        <div v-if="form.errors.description" class="text-danger mt-1">
                            {{ form.errors.description }}
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="main-button" type="submit" :disabled="form.processing">Salva</button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
    @use '../../../scss/app.scss' as *;
</style>
