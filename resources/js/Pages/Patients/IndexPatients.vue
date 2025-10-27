<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>
<template lang="">
    <Head title="Pazienti" />
    <AuthenticatedLayout section="patients">
        <div>Pazienti</div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped></style>
