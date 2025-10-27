<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref } from "vue";
</script>
<template lang="">
    <Head title="Specializzazioni"></Head>
    <AuthenticatedLayout section="specialties">
        <div>Lista specializzazioni</div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
