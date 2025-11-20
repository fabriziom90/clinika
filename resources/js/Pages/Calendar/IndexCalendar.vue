<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { VueCal } from "vue-cal";
import "../../../../node_modules/vue-cal/dist/vue-cal.css";
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";
import Modal from "@/Components/Modal.vue";

import PersonForm from "@/Components/PersonForm.vue";

const props = defineProps({
    appointments: Array,
    doctors: Array,
    patients: Array,
    nurses: Array,
    nationalities: Array,
    userIsSuperadmin: Boolean,
});

const $toast = useToast();
const showNewAppointmentModal = ref(false);
const showDeleteModal = ref(false);
const eventToDelete = ref(null);
const newAppointmentData = ref({
    date: null,
    startTime: null,
    doctorId: "",
    patientId: "",
    nurseId: "",
    newPatient: false,
    title: "",
    duration: 30,
    notes: "",
});

const formAppointment = useForm({
    date: null,
    start_time: null,
    doctor_id: "",
    patient_id: "",
    nurse_id: "",
    title: "",
    duration: 30,
    notes: "",
});

const calendarEvents = computed(() => {
    return props.appointments.map((appointment) => ({
        id: appointment.id,
        // start: appointment.start_time.slice(0, 16).replace("T", " "), //
        // end: appointment.end_time.slice(0, 16).replace("T", " "),
        start: isoToLocalDate(appointment.start_time),
        end: isoToLocalDate(appointment.end_time),
        title: appointment.title,
        // content: `Paziente: ${appointment.patient.name} ${appointment.patient.surname}\nMedico: ${appointment.doctor.user.name} ${appointment.doctor.user.surname}`,
        // background: "#f87171", // opzionale, colore evento
    }));
});

const handleCellClick = (clickedTime) => {
    console.log(new Date(clickedTime.cursor.date));
    if (!props.userIsSuperadmin) return;

    newAppointmentData.value.date = formatDateForInput(clickedTime.cursor.date);
    newAppointmentData.value.startTime = formatTime(clickedTime.cursor.date);
    newAppointmentData.value.newPatient = false;
    showNewAppointmentModal.value = true;
};

const handleNewAppointment = () => {
    formAppointment.date = newAppointmentData.value.date;
    formAppointment.start_time = `${newAppointmentData.value.date} ${newAppointmentData.value.startTime}:00`;
    formAppointment.doctor_id = newAppointmentData.value.doctorId;
    formAppointment.patient_id = newAppointmentData.value.patientId;
    formAppointment.nurse_id = newAppointmentData.value.nurseId;
    formAppointment.title = newAppointmentData.value.title;
    formAppointment.duration = newAppointmentData.value.duration;
    formAppointment.notes = newAppointmentData.value.notes;

    formAppointment.post(route("admin.appointments.store"), {
        onSuccess: () => {
            showNewAppointmentModal.value = false;
            // reset dei dati della modale
            Object.assign(newAppointmentData.value, {
                date: null,
                startTime: null,
                doctorId: "",
                patientId: "",
                nurseId: "",
                newPatient: false,
                title: "",
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
};

const handleDeleted = (event) => {
    showDeleteModal.value = false;
};

const openDeleteModal = (eventClicked) => {
    const event = props.appointments.find(
        (e) => e.id === eventClicked.event.id
    );

    if (!event) return;

    showDeleteModal.value = true;
    eventToDelete.value = event;
};

const closeDeleteModal = () => {
    eventToDelete.value = null;
    showDeleteModal.value = false;
};

const handleNewPatient = (newPatient) => {
    // Aggiunge il nuovo paziente alla lista
    props.patients.push(newPatient);

    // Preseleziona nella select
    newAppointmentData.value.patientId = newPatient.id;

    // Chiudi il form di creazione paziente
    newAppointmentData.value.newPatient = false;
};

function isoToLocalDate(iso) {
    const d = new Date(iso);

    return new Date(
        d.getFullYear(),
        d.getMonth(),
        d.getDate(),
        d.getHours(),
        d.getMinutes()
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
    <Head title="Agenda"></Head>
    <AuthenticatedLayout section="calendar">
        <h2>Calendario</h2>

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
            @event-dblclick="openDeleteModal"
        />

        <div
            v-if="showNewAppointmentModal"
            class="modal fade show modal-bg"
            style="display: block"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-between">
                        <h5 class="modal-title">Nuovo Appuntamento</h5>
                        <button
                            type="button"
                            class="close-modal"
                            @click="showNewAppointmentModal = false"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Medico</label>
                                <select
                                    v-model="newAppointmentData.doctorId"
                                    class="form-select"
                                >
                                    <option value="">Seleziona medico</option>
                                    <option
                                        v-for="doctor in doctors"
                                        :key="doctor.id"
                                        :value="doctor.id"
                                    >
                                        {{ doctor.user.name }}
                                        {{ doctor.user.surname }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Infermiere</label>
                                <select
                                    v-model="newAppointmentData.nurseId"
                                    class="form-select"
                                >
                                    <option value="">
                                        Seleziona infermiere
                                    </option>
                                    <option
                                        v-for="nurse in nurses"
                                        :key="nurse.id"
                                        :value="nurse.id"
                                    >
                                        {{ nurse.user.name }}
                                        {{ nurse.user.surname }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Paziente</label>
                                <select
                                    :class="{
                                        'is-invalid':
                                            formAppointment.errors.patient_id,
                                    }"
                                    v-model="newAppointmentData.patientId"
                                    class="form-select"
                                    @change="
                                        newAppointmentData.newPatient =
                                            newAppointmentData.patientId ===
                                            'new'
                                    "
                                >
                                    <option value="">Seleziona paziente</option>
                                    <option
                                        v-for="patient in patients"
                                        :key="patient.id"
                                        :value="patient.id"
                                    >
                                        {{ patient.name }}
                                        {{ patient.surname }}
                                    </option>
                                    <option value="new">Nuovo paziente</option>
                                </select>
                            </div>

                            <div
                                v-if="newAppointmentData.newPatient"
                                class="mt-4"
                            >
                                <PersonForm
                                    :nationalities="nationalities"
                                    formType="patient"
                                    :inlineMode="true"
                                    @savedInline="handleNewPatient"
                                />
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label">Titolo</label>
                                <input
                                    :class="{
                                        'is-invalid':
                                            formAppointment.errors.title,
                                    }"
                                    type="text"
                                    v-model="newAppointmentData.title"
                                    class="form-control"
                                    placeholder="Es: Visita di controllo"
                                />
                            </div>
                            <div class="col-12 col-md-6 mt-3">
                                <label for="" class="form-label"
                                    >Giorno appuntamento</label
                                >
                                <input
                                    :class="{
                                        'is-invalid':
                                            formAppointment.errors.date,
                                    }"
                                    type="date"
                                    class="form-control"
                                    v-model="newAppointmentData.date"
                                    placeholder="Data appuntamento"
                                />
                            </div>
                            <div class="col-12 col-md-6 mt-3">
                                <label for="" class="form-label"
                                    >Orario appuntamento</label
                                >
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="newAppointmentData.startTime"
                                    placeholder="Orario appuntamento"
                                />
                            </div>
                            <div class="col-12 col-md-6 mt-3">
                                <label class="form-label"
                                    >Durata (minuti)</label
                                >
                                <input
                                    type="number"
                                    min="1"
                                    v-model="newAppointmentData.duration"
                                    class="form-control"
                                    placeholder="30"
                                />
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label">Note</label>
                                <textarea
                                    v-model="newAppointmentData.notes"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Inserisci eventuali note..."
                                ></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            class="secondary-button"
                            @click="showNewAppointmentModal = false"
                        >
                            Annulla
                        </button>
                        <button
                            class="main-button"
                            @click="handleNewAppointment"
                        >
                            Salva
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <Modal
            :show="showDeleteModal"
            :item="eventToDelete"
            baseRoute="admin.appointments"
            @close="closeDeleteModal"
            @deleted="handleDeleted"
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
