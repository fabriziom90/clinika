<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TopBar from "@/Components/TopBar.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout section="superadmin">
        <div class="p-6 text-gray-900">You're logged in!</div>
    </AuthenticatedLayout>
</template>
