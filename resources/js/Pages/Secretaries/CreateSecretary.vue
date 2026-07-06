<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PersonForm from "@/Components/PersonForm.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    nationalities: Array,
});
</script>
<template lang="">
    <Head title="Aggiungi dottore"></Head>
    <AuthenticatedLayout section="secretaries">
        <div>
            <h2>Aggiungi segretaria</h2>
        </div>
        <PersonForm
            :nationalities="nationalities"
            formType="secretarie"
        ></PersonForm>
    </AuthenticatedLayout>
</template>
<style lang=""></style>
