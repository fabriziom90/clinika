<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PersonForm from "@/Components/PersonForm.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
    specialties: Array,
});
</script>
<template lang="">
    <Head title="Aggiungi infermiere"></Head>
    <AuthenticatedLayout section="nurses">
        <div>
            <h2>Aggiungi Infermiere</h2>
        </div>
        <PersonForm
            :nationalities="nationalities"
            :specialties="specialties"
            formType="nurse"
        ></PersonForm>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
