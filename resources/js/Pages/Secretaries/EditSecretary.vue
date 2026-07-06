<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PersonForm from "@/Components/PersonForm.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";



const props = defineProps({
    nationalities: Array,
    secretary: Object
});
</script>
<template lang="">
    <Head title="Modifica segretaria"></Head>
    <AuthenticatedLayout section="secretaries">
        <div>
            <h2>Modifica segretaria</h2>
        </div>
        <PersonForm
            :nationalities="nationalities"
            formType="secretarie"
            :person="secretary"
        ></PersonForm>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
