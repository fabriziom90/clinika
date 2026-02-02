<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Calendar from "@/Components/Calendar.vue";

const props = defineProps({
    appointments: Array,
    doctors: Array,
    patients: Array,
    nurses: Array,
    nationalities: Array,
    userIsSuperadmin: Boolean,
});


</script>

<template lang="">
    <Head title="Agenda"></Head>
    <AuthenticatedLayout section="calendar">
        
        <h2>Calendario</h2>

        <Calendar
            :appointments="appointments"
            :userIsSuperadmin="userIsSuperadmin"
            :doctors="doctors"
            :nurses="nurses"
            :patients="patients"
            :doctor="null"
        />
    </AuthenticatedLayout>
</template>

<style lang="scss">
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;
@use "../../../scss/_partials/mixins" as *;

.vuecal--default-theme {
    height: 900px;
}

.vuecal__views-bar {
    background-color: $mainRed;
    button {
        color: #fff;
    }

    .vuecal__title {
        color: #fff;
    }
}

.vuecal__cell-events {
    width: 100%;
    .vuecal__event {
        background-color: $mainRedHover;
        border-color: $mainRed;
        .vuecal__event-details {
            font-size: 20px;
            .vuecal__event-title {
                padding-bottom: 5px;
            }
            .vuecal__event-time {
                border-top: 1px solid #fff;
                padding-top: 5px;
                font-size: 14px;
            }
        }
    }
}

.vuecal--default-theme.vuecal--light
    .vuecal__weekday:not(.vuecal__weekday--today)
    .vuecal__weekday-date {
    background-color: $mainRedHover;
    color: #fff;
}

.vuecal--default-theme .vuecal__weekday--today .vuecal__weekday-date {
    background-color: $mainRed;
    color: #fff;
}

.vuecal__title-bar {
    background-color: $mainRedHover !important;
}

.vuecal__cell--has-events {
    background-color: rgba(197, 50, 55, 0.46);
}

.vuecal__view-btn--active {
    border-bottom-width: 2px;
    background-color: $mainRedHover;
    border-bottom-color: $mainRed;
    color: #fff;
}

.event-delete-button {
    position: absolute;
    top: -15px;
    right: 0px;

    .delete-event-btn {
        border-radius: 50%;
        background-color: red;
        color: #fff;
        border: 1px solid #fff;
        width: 30px;
        height: 30px;
        transition: 0.3s;
        &:hover {
            background-color: darkred;
        }
    }
}

/* NUOVA VERSIONE: Stili modal */
.modal-bg {
    background-color: rgba(0, 0, 0, 0.4);

    .modal-header {
        background-color: $mainRed;
        color: #fff;

        .close-modal {
            background-color: transparent;
            border: none;
            color: #fff !important;
        }
    }

    .modal-dialog {
        max-width: 800px;
    }
}
</style>
