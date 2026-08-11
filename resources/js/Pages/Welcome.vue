<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>

    <Head title="Welcome" />
    <h1 class="text-center">Clinika</h1>
    <div v-if="canLogin">
        <Link v-if="$page.props.auth.user" :href="route('dashboard')">Dashboard</Link>

        <template v-else>
            <Link :href="route('login')">Log in</Link>


        </template>
    </div>
</template>

<style></style>
