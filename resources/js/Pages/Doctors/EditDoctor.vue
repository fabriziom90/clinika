<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PersonForm from "@/Components/PersonForm.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    specialties: Array,
    doctor: Object,
});
</script>
<template lang="">
    <Head title="Modifica dottore"></Head>
    <AuthenticatedLayout section="doctors">
        <div>
            <h2>Modifica dottore</h2>
        </div>
        <PersonForm
            :nationalities="nationalities"
            :specialties="specialties"
            formType="doctor"
            :person="doctor"
        ></PersonForm>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
