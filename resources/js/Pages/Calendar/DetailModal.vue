<script setup>
import { ref, defineEmits } from "vue";

const emit = defineEmits(["close"]);
const props = defineProps({
    show: Boolean,
    item: Object,
});

const formatDate = (date) => {
    const dateObj = new Date(date);
    const year = dateObj.getFullYear();
    const month = dateObj.getMonth() + 1;
    const day = dateObj.getDate();

    return `${day}/${month}/${year}`;
};

const formatHour = (date) => {
    const dateObj = new Date(date);
    const hours = dateObj.getHours();
    const minutes = dateObj.getMinutes();

    return `${hours}:${minutes}`;
};
</script>
<template lang="">
    <div v-if="show" class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dettaglio appuntamento</h5>
                    <button
                        type="button"
                        class="btn-close"
                        @click="$emit('close')"
                    ></button>
                </div>
                <div class="modal-body">
                    <h3>
                        {{ item.title }}
                    </h3>
                    <div class="infos">
                        <strong>Giorno ed ora</strong>:
                        {{ formatDate(item.start_time) }} -
                        {{
                            `${formatHour(item.start_time)}/${formatHour(
                                item.end_time
                            )}`
                        }}
                    </div>
                    <hr />
                    <p v-if="item.doctor">
                        <strong>Dottore</strong>:
                        {{
                            `${item.doctor.user.name} ${item.doctor.user.surname}`
                        }}
                    </p>
                    <p v-if="item.nurse">
                        <strong>Infermiere</strong>:
                        {{
                            `${item.nurse.user.name} ${item.nurse.user.surname}`
                        }}
                    </p>
                    <p>
                        <strong>Paziente</strong>:
                        {{ `${item.patient.name} ${item.patient.surname}` }}
                    </p>
                    <p v-if="item.note">
                        <strong>Note</strong>:
                        {{ item.note }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button class="secondary-button" @click="$emit('close')">
                        Chiudi
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use "../../../scss/_partials/variables" as *;
h3 {
    color: $mainRed;
    margin-bottom: 0px;
}
strong {
    color: $mainRed;
}

.infos {
    font-size: 15px;
    font-style: italic;
}
</style>
