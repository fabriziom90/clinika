<script setup>
import { ref } from "vue";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";

const props = defineProps({
    events: { type: Array, default: () => [] },
});

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: "timeGridWeek",
    locale: "it",
    events: props.events,

    headerToolbar: {
        left: "timeGridWeek,dayGridMonth",
        center: "",
        right: "prev,next",
    },

    buttonText: {
        dayGridMonth: "Mese",
        timeGridWeek: "Settimana",
    },
    allDayText: "Intero",
});
</script>

<template>
    <FullCalendar :options="calendarOptions" />
</template>

<style lang="scss">
@use "../../scss/app.scss";
@use "../../scss/_partials/variables" as *;
@use "../../scss/_partials/mixins" as *;

.fc .fc-button {
    background-color: $mainRed;
    border: 1px solid $mainRed;
    transition: 0.3s;
    &:hover {
        background-color: $mainRedHover;
        border: 1px solid $mainRedHover;
    }
}

.fc .fc-button:not(:disabled).fc-button-active {
    background-color: $mainGreen;
    border: 1px solid $mainGreen;
}
</style>
