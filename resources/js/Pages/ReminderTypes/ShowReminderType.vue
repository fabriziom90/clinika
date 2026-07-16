<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    reminderType: Object
})

</script>
<template lang="">
    <Head title="Dettaglio tipologia reminder"/>
    <AuthenticatedLayout section="remindertypes">
        <div class="row">
            <div class="col-12">
                <h2>Dettaglio tipologia di promemoria</h2>
            </div>
        </div>
        <div class="row gy-3">
            <div class="col-12 col-md-6">
                <label for="" class="form-label"><strong>Nome</strong></label>
                <div>{{ reminderType.name }}</div>
                <hr>
                <label for="" class="form-label"><strong>Codice</strong></label>
                <div>{{ reminderType.code }}</div>
                <hr>
                <label for="" class="form-label"><strong>Tempistiche</strong></label>
                <div>{{ reminderType.sent_before_value }} {{ reminderType.sent_before_unit === 'hours' ? 'Ore' : 'Giorni' }}</div>
                <hr>
                <label for="" class="form-label"><strong>Attivo</strong></label>
                <div class="rounded-active" :class="reminderType.active ? 'bg-success' : 'bg-black'">{{ reminderType.active ? 'Sì' : 'No'}}</div>
                <hr>
            </div>
            <div class="col-12 col-md-6">
                <div v-if="reminderType.subject != null">
                <label for="" class="form-label"><strong>Oggetto</strong></label>
                <div>{{ reminderType.subject }}</div>
                </div>
                <label for="" class="form-label"><strong>Messaggio</strong></label>
                <div>{{ reminderType.message }}</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use '../../../scss/app.scss' as *;
@use '../../../scss/_partials/variables' as *;

.rounded-active {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    color: #fff;
    font-weight: 700;
}
</style>
