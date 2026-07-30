<script setup>
import { formatDate } from '@/utilities/formatDateFunction';
import CommonListTab from '../CommonListTab.vue';

const props = defineProps({
    appointments: Array
})

const getTime = (dateString) => {
    const date = new Date(dateString);
    const h = String(date.getHours());
    let m = String(date.getMinutes());
    if (m === 0) {
        m = "00"
    }
    else if (m < 10) {
        m = `0${m}`
    }

    return `${h}:${m}`;
}

const statusLabels = {
    'scheduled': {
        'label': 'Prenotato',
        'color': 'warning'
    },
    'completed': {
        'label': 'Completato',
        'color': 'success'
    },
    'cancelled': {
        'label': 'Cancellato',
        'color': 'danger'
    },
    'no-show': {
        'label': 'Assente',
        'color': 'grey'
    }
}
</script>
<template>
    <CommonListTab title="Appuntamenti odierni">
        <div v-if="appointments.length === 0">
            <h4>Non hai nessun appuntamento per oggi</h4>
        </div>
        <ul v-else class="list-unstyled list-group list-group-flush">
            <li class="list-group-item" v-for="appointment in appointments" :key="appointment.id">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-auto">
                        <h4>{{ appointment.patient.name }} {{ appointment.patient.surname }}</h4>
                        <div>{{ appointment.service.name }}</div>
                    </div>
                    <div class="txt-main-red fs-2">
                        <div>{{ formatDate(appointment.start_time) }} {{ getTime(appointment.start_time) }}</div>
                        <div :class="`float-end badge rounded-pill text-bg-${statusLabels[appointment.status].color}`">
                            {{
                                statusLabels[appointment.status].label }}</div>
                    </div>
                </div>
            </li>
        </ul>
    </CommonListTab>
</template>

<style lang="scss" scoped>
@use '../../../scss/_partials/variables' as *;
@use '../../../scss/app.scss' as *;
</style>