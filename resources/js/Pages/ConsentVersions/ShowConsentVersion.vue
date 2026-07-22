<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    consentType: {
        type: Object,
        required: true,
    },
    consentVersion: {
        type: Object,
        required: true,
    },
});

const formatDate = (date) => {
    if (!date) return "-";

    return new Date(date).toLocaleDateString("it-IT", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};
</script>

<template>
    <Head :title="`Versione ${consentVersion.version} - ${consentType.name}`" />

    <AuthenticatedLayout section="consentversions">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Versione {{ consentVersion.version }}</h2>

                    <Link class="main-button" :href="route('admin.consent-types.consent-versions.index', consentType.id)">
                        Torna alle versioni
                    </Link>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card p-4">
                    <h3>{{ consentType.name }}</h3>

                    <div class="mt-3">
                        <strong>Versione:</strong>
                        {{ consentVersion.version }}
                    </div>

                    <div class="mt-2">
                        <strong>Stato: </strong>
                        <span :class="consentVersion.is_active ? 'text-success' : 'text-secondary'">
                            {{ consentVersion.is_active ? "Attiva" : "Non attiva" }}
                        </span>
                    </div>

                    <div class="mt-2">
                        <strong>Pubblicata il:</strong>
                        {{ formatDate(consentVersion.published_at) }}
                    </div>
                    <hr class="my-4">
                    <h4>Contenuto dell'informativa</h4>

                    <div class="consent-content mt-3">
                        {{ consentVersion.content }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped>
@use "../../../scss/app.scss" as *;

</style>
