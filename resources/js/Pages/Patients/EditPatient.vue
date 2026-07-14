<script setup>
import PersonForm from "@/Components/PersonForm.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Head } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    reminderTypes: Array,
    patient: Object,
});

const $toast = useToast();
</script>
<template lang="">
    <Head title="Modifica paziente"></Head>
    <AuthenticatedLayout section="patients">
        <div>
            <h2>Modifica paziente</h2>
        </div>
        <PersonForm
            :nationalities="nationalities"
            :reminderTypes="reminderTypes"
            formType="patient"
            :person="patient"
        />
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;
</style>
