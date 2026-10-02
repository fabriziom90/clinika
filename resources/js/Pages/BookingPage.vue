<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { VueCal } from 'vue-cal';
import Footer from '@/Components/Footer.vue';
import "../../../node_modules/vue-cal/dist/vue-cal.css";

const props = defineProps({
    clinics: Array
})

const selectedClinic = ref(null);
const selectedDoctor = ref(null);
const selectedService = ref(null);

const selectedDate = ref('');
const selectedTime = ref(null);

const name = ref('');
const surname = ref('');
const email = ref('');
const phone = ref('');
const firstVisit = ref(false);

const doctors = ref([]);
const services = ref([]);
const appointments = ref([]);

const loadingDoctors = ref(false);
const loadingAppointments = ref(false);
const loadingServices = ref(false);
const loadingBooking = ref(false);

const bookingErrors = ref({});

const calendarEvents = ref([]);

const loadDoctors = async () => {
    if (!selectedClinic.value) {
        doctors.value = []
        selectedDoctor.value = null
        appointments.value = []
        services.value = []
        calendarEvents.value = []
        selectedService.value = null
        selectedDate.value = ''
        selectedTime.value = null
        return
    }

    loadingDoctors.value = true

    try {
        const response = await fetch(
            `/clinics/${selectedClinic.value}/doctors`,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {
            throw new Error('Errore nel caricamento dei medici')
        }

        doctors.value = await response.json()
        selectedDoctor.value = null
        appointments.value = []
        services.value = []
        calendarEvents.value = []
        selectedService.value = null
        selectedDate.value = ''
        selectedTime.value = null
    } catch (error) {
        console.error(error)
        doctors.value = []
    } finally {
        loadingDoctors.value = false
    }
}

const loadDoctorData = async () => {
    if (!selectedDoctor.value) {
        services.value = []
        appointments.value = []
        calendarEvents.value = []
        selectedService.value = null
        selectedTime.value = null
        return
    }

    loadingAppointments.value = true

    try {
        const [servicesResponse, appointmentsResponse] = await Promise.all([
            fetch(
                `/clinics/${selectedClinic.value}/doctors/${selectedDoctor.value}/services`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            ),
            fetch(
                `/clinics/${selectedClinic.value}/doctors/${selectedDoctor.value}/appointments`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            )
        ])

        if (!servicesResponse.ok || !appointmentsResponse.ok) {
            throw new Error('Errore nel caricamento dei dati del medico')
        }

        services.value = await servicesResponse.json()

        appointments.value = await appointmentsResponse.json()

        calendarEvents.value = appointments.value.map(appointment => ({
            id: appointment.id,
            start: isoToLocalDate(appointment.start),
            end: isoToLocalDate(appointment.end),
            title: 'Occupato'
        }))

        selectedService.value = null
        selectedTime.value = null
    } catch (error) {
        console.error(error)

        services.value = []
        appointments.value = []
        calendarEvents.value = []
        selectedService.value = null
        selectedTime.value = null
    } finally {
        loadingAppointments.value = false
    }
}

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

const timeSlots = computed(() => {
    const slots = [];

    for (let minutes = 7 * 60; minutes <= 22 * 60; minutes += 30) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        slots.push(
            `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`
        );
    }

    return slots;
});

const selectedServiceData = computed(() => {
    if (!selectedService.value) {
        return null;
    }

    return services.value.find(
        service => service.id === selectedService.value
    ) || null;
});

const isTimeDisabled = (time) => {
    if (!selectedDate.value || !selectedServiceData.value) {
        return false;
    }

    const duration = Number(
        selectedServiceData.value.duration_minutes
    );

    if (!duration || duration <= 0) {
        return false;
    }

    const slotStart = new Date(
        `${selectedDate.value}T${time}:00`
    );

    const slotEnd = new Date(
        slotStart.getTime() + duration * 60 * 1000
    );

    const closingTime = new Date(
        `${selectedDate.value}T22:00:00`
    );

    /*
     * La visita non può terminare dopo le 22:00.
     */
    if (slotEnd > closingTime) {
        return true;
    }

    return appointments.value.some(appointment => {
        const appointmentStart = new Date(appointment.start);
        const appointmentEnd = new Date(appointment.end);

        /*
         * Controlliamo solo gli appuntamenti della data selezionata.
         */
        if (
            appointmentStart.toDateString() !== slotStart.toDateString() &&
            appointmentEnd.toDateString() !== slotStart.toDateString()
        ) {
            return false;
        }

        /*
         * Due intervalli si sovrappongono quando:
         *
         * inizio nuova visita < fine appuntamento
         * &&
         * fine nuova visita > inizio appuntamento
         */
        return (
            slotStart < appointmentEnd &&
            slotEnd > appointmentStart
        );
    });
};

const onDateChange = () => {
    selectedTime.value = null;
};

const onServiceChange = () => {
    selectedTime.value = null;
};

const submitBooking = async () => {
    bookingErrors.value = {};


    if (
        !selectedClinic.value ||
        !selectedDoctor.value ||
        !selectedService.value ||
        !selectedDate.value ||
        !selectedTime.value
    ) {
        bookingErrors.value = {
            booking: 'Completa tutti i dati della prenotazione.'
        };

        return;
    }

    loadingBooking.value = true;

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const response = await fetch('/', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify({
                clinic_id: selectedClinic.value,
                doctor_id: selectedDoctor.value,
                service_id: selectedService.value,
                date: selectedDate.value,
                time: selectedTime.value,
                name: name.value,
                surname: surname.value,
                email: email.value,
                phone: phone.value,
                first_visit: firstVisit.value,
            }),
        });

        const responseData = await response.json();

        if (responseData.success) {
            window.location.href = responseData.redirect;
            return;
        }

        if (response.status === 422) {
            bookingErrors.value = responseData.errors || {};

            return;
        }

        if (!response.ok) {
            throw new Error(
                responseData.message || 'Errore durante la prenotazione.'
            );
        }

        selectedDate.value = '';
        selectedTime.value = null;

        name.value = '';
        surname.value = '';
        email.value = '';
        phone.value = '';
        firstVisit.value = false;

        appointments.value = await fetch(
            `/clinics/${selectedClinic.value}/doctors/${selectedDoctor.value}/appointments`,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        ).then(response => response.json());

        calendarEvents.value = appointments.value.map(appointment => ({
            id: appointment.id,
            start: isoToLocalDate(appointment.start),
            end: isoToLocalDate(appointment.end),
            title: 'Occupato'
        }));

    } catch (error) {
        console.error(error);

        bookingErrors.value = {
            booking: error.message || 'Errore durante la prenotazione.'
        };
    } finally {
        loadingBooking.value = false;
    }
    console.log(bookingErrors)
};
</script>

<template>

    <Head title="Prenota il tuo appuntamento" />

    <header class="bg-main-red">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between py-3">
                        <div>
                            <ApplicationLogo width="75px" color="#fff" title="Clinika - Prenotazione appuntamento" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="my-5">

        <div class="container">
            <div class="row gy-5">
                <div class="col-12 text-center">
                    <h1 class="txt-main-red">
                        Prenota il tuo appuntamento
                    </h1>

                    <h4>
                        Scegli la struttura ed il medico con cui vuoi prenotare
                        una visita. Inserisci i tuoi dati ed clicca sul pulsante
                        di conferma. Se è tutto corretto riceverai una mail di
                        conferma avvenuta prenotazione.
                    </h4>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-5">

            <div class="row">

                <div class="col-12 col-md-8">

                    <vue-cal :time="true" default-view="week" locale="it" :views="['week', 'month']" :titlebar="false"
                        :events="calendarEvents" :today-button="false" :time-cell-height="120" :time-from="7 * 60"
                        :time-to="22 * 60" :editable-events="{
                            title: false,
                            drag: false,
                            resize: false,
                            delete: false,
                        }">
                        <template #event="{ event }">
                            <div class="event-wrapper">
                                <div class="d-flex">
                                    <div class="event-title">
                                        {{ event.title }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </vue-cal>

                </div>

                <div class="col-12 col-md-4">

                    <div v-if="bookingErrors.booking" class="alert alert-danger">
                        {{ bookingErrors.booking }}
                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Struttura
                        </label>

                        <select class="form-select" @change="loadDoctors" v-model="selectedClinic">
                            <option :value="null">
                                Seleziona struttura
                            </option>

                            <option :value="clinic.id" v-for="clinic in clinics" :key="clinic.id">
                                {{ clinic.name }}
                            </option>
                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Medico
                        </label>

                        <select class="form-select" @change="loadDoctorData" v-model="selectedDoctor"
                            :disabled="!selectedClinic || loadingDoctors">
                            <option :value="null">
                                Seleziona dottore
                            </option>

                            <option :value="doctor.id" v-for="doctor in doctors" :key="doctor.id">
                                {{ doctor.name }}
                                {{ doctor.surname }}

                                <template v-if="doctor.specialty">
                                    - {{ doctor.specialty.name }}
                                </template>
                            </option>
                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Tipologia di visita
                        </label>

                        <select v-model="selectedService" class="form-select"
                            :disabled="!selectedDoctor || !services.length" @change="onServiceChange">
                            <option :value="null">
                                Seleziona tipologia di visita
                            </option>

                            <option v-for="service in services" :key="service.id" :value="service.id">
                                {{ service.name }}
                            </option>
                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Data
                        </label>

                        <input type="date" class="form-control" v-model="selectedDate"
                            :disabled="!selectedDoctor || !selectedService"
                            :min="new Date().toISOString().split('T')[0]" @change="onDateChange" />

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Ora
                        </label>

                        <select v-model="selectedTime" class="form-select"
                            :disabled="!selectedDate || !selectedService">
                            <option :value="null">
                                Seleziona ora
                            </option>

                            <option v-for="time in timeSlots" :key="time" :value="time"
                                :disabled="isTimeDisabled(time)">
                                {{ time }}

                                <template v-if="isTimeDisabled(time)">
                                    - Occupato
                                </template>
                            </option>
                        </select>

                    </div>

                    <div v-if="selectedServiceData && selectedDate" class="alert alert-light border">
                        <div>
                            <strong>Durata visita:</strong>
                            {{ selectedServiceData.duration_minutes }} minuti
                        </div>

                        <div v-if="selectedTime">
                            <strong>Orario:</strong>
                            {{ selectedTime }}
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">
                        I tuoi dati
                    </h5>

                    <div class="mb-3">

                        <label class="form-label">
                            Nome
                        </label>

                        <input type="text" class="form-control" v-model="name" autocomplete="given-name" />

                        <div v-if="bookingErrors.name" class="text-danger small mt-1">
                            {{ bookingErrors.name[0] }}
                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Cognome
                        </label>

                        <input type="text" class="form-control" v-model="surname" autocomplete="family-name" />

                        <div v-if="bookingErrors.surname" class="text-danger small mt-1">
                            {{ bookingErrors.surname[0] }}
                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input type="email" class="form-control" v-model="email" autocomplete="email" />

                        <div v-if="bookingErrors.email" class="text-danger small mt-1">
                            {{ bookingErrors.email[0] }}
                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Telefono
                        </label>

                        <input type="tel" class="form-control" v-model="phone" autocomplete="tel" />

                        <div v-if="bookingErrors.phone" class="text-danger small mt-1">
                            {{ bookingErrors.phone[0] }}
                        </div>

                    </div>

                    <div class="form-check mb-4">

                        <input id="first_visit" type="checkbox" class="form-check-input" v-model="firstVisit" />

                        <label for="first_visit" class="form-check-label">
                            È la mia prima visita
                        </label>

                        <div v-if="bookingErrors.first_visit" class="text-danger small mt-1">
                            {{ bookingErrors.first_visit[0] }}
                        </div>

                    </div>

                    <button type="button" class="main-button w-100" :disabled="loadingBooking" @click="submitBooking">
                        <span v-if="loadingBooking" class="spinner-border spinner-border-sm me-2" role="status"></span>

                        {{ loadingBooking ? 'Prenotazione in corso...' : 'Conferma prenotazione' }}
                    </button>

                </div>

            </div>

        </div>

    </main>
    <Footer />

</template>

<style lang="scss">
@use '../../scss/app.scss' as *;
@use '../../scss/_partials/variables' as *;

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
