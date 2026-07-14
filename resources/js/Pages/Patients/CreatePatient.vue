<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PersonForm from "@/Components/PersonForm.vue";
import { Head } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    reminderTypes: Array,
});


</script>
<template lang="">
    <Head title="Aggiungi paziente"></Head>
    <AuthenticatedLayout section="patients">
        <div>
            <h2>Aggiungi paziente</h2>
        </div>
        <PersonForm
            :nationalities="nationalities"
            :reminderTypes="reminderTypes"
            formType="patient"
        ></PersonForm>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss" as *;
</style>
