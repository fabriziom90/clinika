<script setup>
import { VueCal } from "vue-cal";
import "../../../node_modules/vue-cal/dist/vue-cal.css";
import { ref, computed, watch, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";
import Modal from "@/Components/Modal.vue";
import DetailModal from "@/Pages/Calendar/DetailModal.vue";
import { useConfigStore } from "@/stores/main";
import PersonForm from "@/Components/PersonForm.vue";
import { useAppointmentStore } from "@/stores/appointments";

// debounce variable to handle click or doubleClick
let clickTimeout = null;

const props = defineProps({
    appointments: Array,
    doctor: Object,
    doctors: Array,
    patients: Array,
    nurses: Array,
    nationalities: Array,
    userCanCreateAppointment: Boolean,
});

const configStore = useConfigStore();
const appointmentsStore = useAppointmentStore();

const $toast = useToast();
// variable to show new appointment modal
const showNewAppointmentModal = ref(false);

// variables for deletion
const showDeleteModal = ref(false);
const eventToDelete = ref(null);

// variable for showing details
const showDetailModal = ref(false);
const eventToShow = ref(null);

// variable for dragging
const editingEventId = ref(null);
const isEditingAppointment = ref(false);
const suppressClickAfterDrag = ref(false);

//check if there is an event handling
const isInteractingWithEvent = ref(false);

// new appointment data
const newAppointmentData = ref({
    date: null,
    startTime: null,
    doctorId: props.doctor?.id ?? "",
    serviceId: "",
    patientId: "",
    nurseId: "",
    newPatient: false,
    duration: 30,
    notes: "",
});

// form appointment
const formAppointment = useForm({
    date: null,
    start_time: null,
    doctor_id: props.doctor?.id,
    patient_id: "",
    nurse_id: "",
    service_id: "",
    duration: 30,
    notes: "",
});

onMounted(() => {
    appointmentsStore.setAppointments(props.appointments);

});

// computed property to show events in calendar
const calendarEvents = computed(() => {

    return appointmentsStore.items.map((appointment) => ({
        id: appointment.id,
        start: isoToLocalDate(appointment.start_time),
        end: isoToLocalDate(appointment.end_time),
        title:
            configStore.user.roles[0] === "admin"
                ? `${appointment.doctor.user.name} ${appointment.doctor.user.surname}`
                : appointment.service?.name,
    }));
});

// computed property to get doctor's services
const availableServices = computed(() => {
    if (props.doctor) {
        return props.doctor.services ?? [];
    }

    if (!newAppointmentData.value.doctorId) return [];

    const doc = props.doctors.find(
        (doctor) => doctor.id === newAppointmentData.value.doctorId,
    );

    return doc?.services ?? [];
});

//watch to prepopulate duration
watch(
    () => newAppointmentData.value.serviceId,
    (serviceId) => {
        if (!serviceId) return;

        if (isEditingAppointment.value) return;
        const service = availableServices.value.find((s) => s.id === serviceId);

        if (!service) return;
        newAppointmentData.value.duration = service.pivot.duration_minutes;
    },
);

// click on empty cell in calendar to get date and time and show modal for new appointment insertion
const handleCellClick = (clickedTime) => {
    if (!props.userCanCreateAppointment) return;

    if (isInteractingWithEvent.value) return;

    newAppointmentData.value.date = formatDateForInput(clickedTime.cursor.date);
    newAppointmentData.value.startTime = formatTime(clickedTime.cursor.date);
    newAppointmentData.value.newPatient = false;
    showNewAppointmentModal.value = true;
};

// function that create new appointment
const handleNewAppointment = () => {
    formAppointment.date = newAppointmentData.value.date;
    formAppointment.start_time = `${newAppointmentData.value.date} ${newAppointmentData.value.startTime}:00`;
    formAppointment.doctor_id = newAppointmentData.value.doctorId;
    formAppointment.patient_id = newAppointmentData.value.patientId;
    formAppointment.nurse_id = newAppointmentData.value.nurseId;
    formAppointment.service_id = newAppointmentData.value.serviceId;
    formAppointment.duration = newAppointmentData.value.duration;
    formAppointment.notes = newAppointmentData.value.notes;

    if (editingEventId.value) {
        // update esistente
        formAppointment.patch(
            route("admin.appointments.update", editingEventId.value),
            {
                onSuccess: (page) => {
                    showNewAppointmentModal.value = false;
                    // reset editing flag
                    editingEventId.value = null;

                    //update store
                    appointmentsStore.setAppointments(page.props.appointments);

                    // reset modale dati (come già fai)
                    Object.assign(newAppointmentData.value, {
                        date: null,
                        startTime: null,
                        doctorId: "",
                        serviceId: "",
                        patientId: "",
                        nurseId: "",
                        newPatient: false,
                        duration: 30,
                        notes: "",
                    });
                },
                onError: (err) => {
                    console.log(err);
                    const flat = Object.values(err).flat();
                    $toast.error(flat.join("<br>"));
                    onSaving.value = false;
                },
            },
        );
    } else {
        formAppointment.post(route("admin.appointments.store"), {
            onSuccess: (page) => {
                showNewAppointmentModal.value = false;

                appointmentsStore.setAppointments(page.props.appointments);


                // reset dei dati della modale
                Object.assign(newAppointmentData.value, {
                    date: null,
                    startTime: null,
                    doctorId: "",
                    serviceId: "",
                    patientId: "",
                    nurseId: "",
                    newPatient: false,
                    duration: 30,
                    notes: "",
                });
            },
            onError: (err) => {
                console.log(err);
                const flat = Object.values(err).flat();
                $toast.error(flat.join("<br>"));
                onSaving.value = false;
            },
        });
    }
};

// define function to handle single click
const handleEventClick = (event) => {
    // suppress opening other modals
    if (suppressClickAfterDrag.value) {
        // ignora click subito dopo drag
        suppressClickAfterDrag.value = false;
        return;
    }

    clearTimeout(clickTimeout);

    // wait if there is a double click
    clickTimeout = setTimeout(() => {
        clickTimeout = null;

        openDetailModal(event);
    }, 180);
};

// define function to handle double click
const handleEventDblClick = (event) => {
    clearTimeout(clickTimeout);

    clickTimeout = null;

    openDeleteModal(event);
};

// function that close delete modal
const handleDeleted = (event) => {

    showDeleteModal.value = false;
    appointmentsStore.remove(eventToDelete.value.id);
};

// function that open delete modal
const openDeleteModal = (eventClicked) => {
    const event = appointmentsStore.items.find(
        (e) => e.id === eventClicked.event.id,
    );

    if (!event) return;

    eventToDelete.value = event;
    showDeleteModal.value = true;
};

// function that close delete modal
const closeDeleteModal = () => {
    eventToDelete.value = null;
    showDeleteModal.value = false;
};

// function that open detail modal
const openDetailModal = (eventClicked) => {
    appointmentsStore.select(eventClicked.event.id);

    showDetailModal.value = true;
};

// function that close detail modal
const closeDetailModal = () => {
    appointmentsStore.select(null);
    showDetailModal.value = false;
};

// function that handles drag and drop
const openEditFromCalendar = (payload) => {
    // normalizing payload vuecal
    const ev = payload?.event ?? payload?.originalEvent ?? payload;


    if (!ev || !ev.id) {
        console.error("Evento non valido", payload);
        return;
    }

    // get complete event from backend
    const appointment = appointmentsStore.items.find(
        (a) => String(a.id) === String(ev.id),
    );

    if (!appointment) {
        console.error("Appuntamento non trovato", ev.id);
        return;
    }

    editingEventId.value = appointment.id;
    isEditingAppointment.value = true;

    // date from drag
    const start = new Date(ev.start);
    const end = new Date(ev.end);

    const durationMinutes = Math.max(1, Math.round((end - start) / 60000));

    // complete
    newAppointmentData.value = {
        date: formatDateForInput(start),
        startTime: formatTime(start),
        doctorId: appointment.doctor_id ?? appointment.doctor?.id ?? "",
        patientId: appointment.patient_id ?? appointment.patient?.id ?? "",
        nurseId: appointment.nurse_id ?? appointment.nurse?.id ?? "",
        serviceId: appointment.service_id ?? appointment.service?.id ?? "",
        newPatient: false,
        duration: durationMinutes,
        notes: appointment.notes ?? "",
    };

    // Inertia form
    formAppointment.date = newAppointmentData.value.date;
    formAppointment.start_time = `${newAppointmentData.value.date} ${newAppointmentData.value.startTime}:00`;
    formAppointment.doctor_id = newAppointmentData.value.doctorId;
    formAppointment.patient_id = newAppointmentData.value.patientId;
    formAppointment.nurse_id = newAppointmentData.value.nurseId;
    formAppointment.service_id = newAppointmentData.value.serviceId;
    formAppointment.duration = newAppointmentData.value.duration;
    formAppointment.notes = newAppointmentData.value.notes;

    showNewAppointmentModal.value = true;

    suppressClickAfterDrag.value = true;
    setTimeout(() => (suppressClickAfterDrag.value = false), 300);
};

// wrapper function for edit event from button
const editFromButton = (event) => {
    openEditFromCalendar({
        event,
    });
};

// function that create new patient from modal
const handleNewPatient = (newPatient) => {
    // Aggiunge il nuovo paziente alla lista
    props.patients.push(newPatient);

    // Preseleziona nella select
    newAppointmentData.value.patientId = newPatient.id;

    // Chiudi il form di creazione paziente
    newAppointmentData.value.newPatient = false;
};

//
function isoToLocalDate(iso) {
    const d = new Date(iso);

    return new Date(
        d.getFullYear(),
        d.getMonth(),
        d.getDate(),
        d.getHours(),
        d.getMinutes(),
    );
}

function formatTime(date) {
    const h = String(date.getHours()).padStart(2, "0");
    const m = String(date.getMinutes()).padStart(2, "0");
    return `${h}:${m}`;
}

function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
}
</script>

<template lang="">
    <vue-cal
        :time="true"
        default-view="week"
        locale="it"
        :views="['week', 'month']"
        :titlebar="false"
        @cell-click="handleCellClick"
        :events="calendarEvents"
        :today-button="false"
        :time-cell-height="120"
        :time-from="7 * 60"
        :time-to="22 * 60"
        @event-click="handleEventClick"
        @event-dblclick="handleEventDblClick"
        @event-drop="openEditFromCalendar"
        @event-change="openEditFromCalendar"
        :editable-events="{
            title: false,
            drag: true,
            resize: false,
            delete: false,
        }"
    >
        <template #event="{ event }">
            <div class="event-wrapper">
                <div class="d-flex">
                    <div class="event-title">
                        {{ event.title }}
                    </div>
                    <div class="event-actions">
                        <button
                            class="btn btn-xs rounded-5 ms-2 btn-light"
                            @click.stop="editFromButton(event)"
                        >
                            <i class="fas fa-pencil"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
</vue-cal>
<div v-if="showNewAppointmentModal" class="modal fade show modal-bg" style="display: block">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title">
                    {{
                    editingEventId
                    ? "Modifica appuntamento"
                    : "Nuovo appuntamento"
                    }}
                </h5>
                <button type="button" class="close-modal" @click="showNewAppointmentModal = false">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Medico</label>

                        <!-- SE siamo nella pagina del medico -->
                        <select v-if="doctor" class="form-select" v-model="newAppointmentData.doctorId" disabled>
                            <option :value="doctor.id">
                                {{ doctor.user.name }}
                                {{ doctor.user.surname }}
                            </option>
                        </select>

                        <!-- SE siamo nell'agenda generale -->
                        <select v-else class="form-select" v-model="newAppointmentData.doctorId" :class="{
                                    'is-invalid':
                                        formAppointment.errors.doctor_id,
                                }">
                            <option value="">Seleziona medico</option>
                            <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                {{ doc.user.name }} {{ doc.user.surname }}
                            </option>
                        </select>

                        <span v-if="formAppointment.errors.doctor_id" class="text-danger">
                            {{ formAppointment.errors.doctor_id }}
                        </span>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">Infermiere</label>
                        <select v-model="newAppointmentData.nurseId" class="form-select">
                            <option value="">Seleziona infermiere</option>
                            <option v-for="nurse in nurses" :key="nurse.id" :value="nurse.id">
                                {{ nurse.user.name }}
                                {{ nurse.user.surname }}
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Paziente</label>
                        <select :class="{
                                    'is-invalid':
                                        formAppointment.errors.patient_id,
                                }" v-model="newAppointmentData.patientId" class="form-select" @change="
                                    newAppointmentData.newPatient =
                                        newAppointmentData.patientId === 'new'
                                ">
                            <option value="">Seleziona paziente</option>
                            <option v-for="patient in patients" :key="patient.id" :value="patient.id">
                                {{ patient.name }}
                                {{ patient.surname }}
                            </option>
                            <option value="new">Nuovo paziente</option>
                        </select>
                        <span v-if="formAppointment.errors.patient_id" class="text-danger">{{
                            formAppointment.errors.patient_id }}</span>
                    </div>

                    <div v-if="newAppointmentData.newPatient" class="mt-4">
                        <PersonForm :nationalities="nationalities" formType="patient" :inlineMode="true"
                            @savedInline="handleNewPatient" />
                    </div>

                    <div class="col-12 col-md-6 mt-3">
                        <label class="form-label">Titolo</label>
                        <select name="" id="" class="form-select" v-model="newAppointmentData.serviceId">
                            <option value="">Seleziona prestazione</option>
                            <option :value="service.id" :key="service.id" v-for="service in availableServices">
                                {{ service.name }}
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mt-3">
                        <label for="" class="form-label">Giorno appuntamento</label>
                        <input :class="{
                                    'is-invalid': formAppointment.errors.date,
                                }" type="date" class="form-control" v-model="newAppointmentData.date"
                            placeholder="Data appuntamento" />
                        <span v-if="formAppointment.errors.date" class="text-danger">{{ formAppointment.errors.date
                            }}</span>
                    </div>
                    <div class="col-12 col-md-6 mt-3">
                        <label for="" class="form-label">Orario appuntamento</label>
                        <input type="text" class="form-control" :class="{
                                    'is-invalid':
                                        formAppointment.errors.start_time,
                                }" v-model="newAppointmentData.startTime" placeholder="Orario appuntamento" />
                        <span v-if="formAppointment.errors.start_time" class="text-danger">{{
                            formAppointment.errors.start_time }}</span>
                    </div>
                    <div class="col-12 col-md-6 mt-3">
                        <label class="form-label">Durata (minuti)</label>
                        <input type="number" min="1" v-model="newAppointmentData.duration" class="form-control" :class="{
                                    'is-invalid':
                                        formAppointment.errors.duration,
                                }" placeholder="30" />
                        <span v-if="formAppointment.errors.duration" class="text-danger">{{
                            formAppointment.errors.duration }}</span>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label">Note</label>
                        <textarea v-model="newAppointmentData.notes" class="form-control" rows="3"
                            placeholder="Inserisci eventuali note..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="secondary-button" @click="showNewAppointmentModal = false">
                    Annulla
                </button>
                <button class="main-button" @click="handleNewAppointment">
                    Salva
                </button>
            </div>
        </div>
    </div>
</div>
<Modal :show="showDeleteModal" :item="eventToDelete" baseRoute="admin.appointments" @close="closeDeleteModal"
    @deleted="handleDeleted" />
<DetailModal :key="eventToShow?.id + '-' + eventToShow?.status" :show="showDetailModal" @close="closeDetailModal" />
</template>

<style lang="scss">
@use "../../scss/app.scss";
@use "../../scss/_partials/variables" as *;
@use "../../scss/_partials/mixins" as *;

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
            font-size: 15px;

            .vuecal__event-title {
                padding-bottom: 5px;
                font-size: 15px;
            }

            .vuecal__event-time {
                border-top: 1px solid #fff;
                padding-top: 5px;
                font-size: 14px;
            }
        }
    }
}

.vuecal--default-theme.vuecal--light .vuecal__weekday:not(.vuecal__weekday--today) .vuecal__weekday-date {
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

.btn-xs {
    font-size: 12px;
    padding: 5px 7px;
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
