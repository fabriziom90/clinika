<script setup>
import { ref, defineEmits, computed } from "vue";
import { useConfigStore } from "@/stores/main";
import { useToast } from "vue-toast-notification";
import { router } from "@inertiajs/vue3";
import { useAppointmentStore } from "@/stores/appointments";

const emit = defineEmits(["close"]);
const props = defineProps({
    show: Boolean,
    item: Object,
});

const appointmentsStore = useAppointmentStore();
const configStore = useConfigStore();

const editingStatus = ref(false);
const selectedStatus = ref("scheduled");
const $toast = useToast();
const item = computed(() => appointmentsStore.selected);

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

const isAdmin = computed(() => configStore.user.roles.includes("admin"));

const getLabel = (status) => {
    const labels = {
        scheduled: "Prenotato",
        completed: "Completato",
        cancelled: "Cancellato",
        no_show: "Assente"
    }

    return labels[status] ?? status;
}

const updateStatus = () => {

    router.put(`/admin/appointments/${item.value.id}/status`, { status: selectedStatus.value }, {
        preserveScroll: true,
        onSuccess: () => {
            appointmentsStore.updateStatus(
                item.value.id,
                selectedStatus.value,
                getLabel(selectedStatus.value)
            )
            editingStatus.value = false;
            $toast.success("Stato appuntamento cambiato correttamente");

        }
    })
}

const generateInvoice = () => {
    router.get(route('admin.appointments.invoice.create', item.value.id));
}
</script>
<template lang="">

    <div v-if="show" class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               
                <div class="modal-header">
                    <h5 class="modal-title">Dettaglio appuntamento -
                        <span class="status-pill me-2" :class="{ 'cursor-default': !isAdmin}" @click="editingStatus = !editingStatus">
                            {{ item.status_label }}
                        </span>
                        <span class="status-pill" v-if="configStore.hasPermission('appointment.create') && item.status === 'completed' && !item.invoice" @click="generateInvoice">
                            Genera fattura
                        </span>
                        <span v-if="configStore.hasPermission('appointment.create') && item.invoice">
                            Visualizza fattura
                        </span>
                    </h5>
                    <div v-if="configStore.hasPermission('appointment.create') && editingStatus" class="status-editor ms-2" >
                        <select class="form-select py-0 my-0" v-model="selectedStatus">
                            <option value="scheduled">Prenotato</option>
                            <option value="completed">Completato</option>
                            <option value="cancelled">Cancellato</option>
                            <option value="no_show">Assente</option>
                        </select>

                        <button class="secondary-button py-0" @click="updateStatus">
                            Salva
                        </button>
                    </div>
                    <button
                        type="button"
                        class="btn-close"
                        @click="$emit('close')"
                    ></button>
                </div>
                <div class="modal-body">
                    <h3>
                        <span v-if="configStore.user.roles[0] === 'admin'">{{item.doctor.user.name}} {{item.doctor.user.surname}}</span>
                        <span v-else>{{ item.service?.name }} - {{ item.service?.code}}</span>
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
                    <p v-if="item.notes">
                        <strong>Note</strong>:
                        {{ item.notes }}
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

.status-pill {
    background: #ffffff;
    color: #C53238;
    padding: 4px 10px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 13px;
    transition: 0.3s;

    &:active,
    &:hover {
        background: #fcd7d9;
    }
}

.status-editor {
    margin-top: 5px;
    display: flex;
    gap: 10px;
    align-items: center;
}
</style>
