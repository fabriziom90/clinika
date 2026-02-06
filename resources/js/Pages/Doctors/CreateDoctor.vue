<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PersonForm from "@/Components/PersonForm.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    specialties: Array,
    services: Array
});
</script>
<template lang="">
    <Head title="Aggiungi dottore"></Head>
    <AuthenticatedLayout section="doctors">
        <div>
            <h2>Aggiungi dottore</h2>
        </div>
        <PersonForm
            :nationalities="nationalities"
            :specialties="specialties"
            :services="services"
            formType="doctor"
        ></PersonForm>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
