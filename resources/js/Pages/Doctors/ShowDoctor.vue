<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { formatDate } from "@/utilities/formatDateFunction";
import Calendar from "@/Components/Calendar.vue";
import ExportToPdfButton from "@/Components/ExportToPdfButton.vue";

import { computed, ref } from "vue";

const props = defineProps({
    doctor: Object,
    doctors: Array,
    nurses: Array,
    nationalities: Array,
    patients: Array,
    userIsAdmin: Boolean,
    userCanCreateAppointment: Boolean,
    compensations: Array
});

const detailsOpen = ref(false);

const showDetail = () => detailsOpen.value = !detailsOpen.value;

const currentDate = new Date();

const compensationMonth = ref(currentDate.getMonth() + 1);
const compensationYear = ref(currentDate.getFullYear());

const months = [
    { value: 1, label: "Gennaio" },
    { value: 2, label: "Febbraio" },
    { value: 3, label: "Marzo" },
    { value: 4, label: "Aprile" },
    { value: 5, label: "Maggio" },
    { value: 6, label: "Giugno" },
    { value: 7, label: "Luglio" },
    { value: 8, label: "Agosto" },
    { value: 9, label: "Settembre" },
    { value: 10, label: "Ottobre" },
    { value: 11, label: "Novembre" },
    { value: 12, label: "Dicembre" }
];

const pdfColumns = ["Prestazione", "Data", "Importo prestazione", "Tipo compenso", "Compenso medico"];

const pdfRows = computed(() =>
    filteredCompensations.value.map(c => [
        c.service?.name,
        formatDate(c.invoice.date),
        `${c.service_amount}€`,
        c.compensation_type === 'fixed' ? "Fisso" : "Percentuale",
        `${c.compensation_value}€`
    ])
);

const compensationYears = computed(() => {
    const years = props.compensations.map((compensation) => {
        return compensation.invoice?.date
            ? new Date(compensation.invoice.date).getFullYear()
            : null;
    });

    years.push(currentDate.getFullYear());

    return [...new Set(years)]
        .filter(Boolean)
        .sort((a, b) => b - a);
});

const filteredCompensations = computed(() => {
    return props.compensations.filter((compensation) => {
        if (!compensation.invoice?.date) {
            return false;
        }

        const date = new Date(compensation.invoice.date);

        return (
            date.getMonth() + 1 === Number(compensationMonth.value) &&
            date.getFullYear() === Number(compensationYear.value)
        );
    });
});

const formatAmount = (amount) => {
    return Number(amount || 0).toFixed(2);
};

const compensationLabel = (compensation) => compensation.compensation_type === "percentage" ? "Percentuale" : "Fisso";


const compensationStatusLabel = (status) => {
    const labels = {
        accrued: "Maturato",
        paid: "Pagato"
    };

    return labels[status] ?? status;
};
</script>

<template>

    <Head title="Dettaglio dottore" />

    <AuthenticatedLayout section="doctors">
        <div class="container-fluid">
            <div class="row gy-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>Dettaglio dottore</h2>
                        </div>

                        <div>
                            <Link class="main-button" :href="route('admin.doctors.edit', doctor.id)">
                                Modifica dottore
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="row">
                        <div class="col-12">
                            <h3>
                                {{ doctor.user.name }} {{ doctor.user.surname }}

                                <i class="fas" :class="detailsOpen ? 'fa-caret-up' : 'fa-caret-down'"
                                    @click="showDetail"></i>
                            </h3>
                        </div>

                        <div class="col-12" v-if="detailsOpen">
                            <div class="bg-main-red text-white">
                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Anagrafica dottore</h3>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Data di nascita</label>
                                        <p>{{ formatDate(doctor.birthday) }}</p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Città di nascita</label>
                                        <p>{{ doctor.birth_city }}</p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Nazionalità</label>
                                        <p>
                                            {{
                                                doctor.nationality === null
                                                    ? "Nazionalità non valorizzata"
                                                    : doctor.nationality.name
                                            }}
                                        </p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Sesso</label>
                                        <p>
                                            {{ doctor.genre === "m" ? "Uomo" : "Donna" }}
                                        </p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Codice fiscale</label>
                                        <p>{{ doctor.personal_code }}</p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Specializzazione</label>
                                        <p>{{ doctor.specialty.name }}</p>
                                    </div>
                                </div>

                                <hr>

                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Residenza</h3>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Città di residenza</label>
                                        <p>{{ doctor.city }}</p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Indirizzo</label>
                                        <p>{{ doctor.address }}</p>
                                    </div>
                                </div>

                                <hr>

                                <div class="row gy-3">
                                    <div class="col-12">
                                        <h3>Informazioni contatto</h3>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Email</label>
                                        <p>{{ doctor.user.email }}</p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Telefono</label>
                                        <p>{{ doctor.phone }}</p>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Pec</label>
                                        <p>{{ doctor.pec }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-7">
                    <div class="bg-main-red mt-3">
                        <div class="row align-items-end mb-4">
                            <div class="col-12 col-md-5">
                                <h3>Compensi</h3>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Mese</label>

                                <select v-model="compensationMonth" class="form-select">
                                    <option v-for="month in months" :key="month.value" :value="month.value">
                                        {{ month.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <label class="form-label">Anno</label>

                                <select v-model="compensationYear" class="form-select">
                                    <option v-for="year in compensationYears" :key="year" :value="year">
                                        {{ year }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <ExportToPdfButton :title="`Compensi ${doctor.user.name} ${doctor.user.surname}`"
                                    :columns="pdfColumns" :rows="pdfRows"
                                    :filename="`compensi_${doctor.user.name}_${doctor.user.surname}.pdf`"
                                    audit-type="App\Models\DoctorCompensation" :audit-id="doctor.id" />
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Prestazione</th>
                                        <th>Fattura</th>
                                        <th>Importo prestazione</th>
                                        <th>Tipo compenso</th>
                                        <th>Compenso</th>
                                        <th>Stato</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="compensation in filteredCompensations" :key="compensation.id">
                                        <td>
                                            {{ formatDate(compensation.invoice?.date) }}
                                        </td>

                                        <td>
                                            {{ compensation.service?.name }}
                                            <br>
                                            <small>
                                                {{ compensation.service?.code }}
                                            </small>
                                        </td>

                                        <td>
                                            {{ compensation.invoice?.number }}
                                        </td>

                                        <td>
                                            {{ formatAmount(compensation.service_amount) }} €
                                        </td>

                                        <td>
                                            {{ compensationLabel(compensation.compensation_type) }}
                                        </td>

                                        <td>
                                            <strong>
                                                {{ formatAmount(compensation.compensation_amount) }} €
                                            </strong>
                                        </td>

                                        <td>
                                            <span class="badge" :class="compensation.status === 'paid'
                                                ? 'bg-success'
                                                : 'bg-warning text-dark'
                                                ">
                                                {{ compensationStatusLabel(compensation.status) }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-if="filteredCompensations.length === 0">
                                        <td colspan="7" class="text-center">
                                            Nessun compenso trovato.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="col-12 col-md-5">
                    <div class="bg-main-red">
                        <div class="row">
                            <div class="col-12">
                                <h3>Prestazioni</h3>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Prestazione</label>
                            </div>

                            <div class="col-12 col-md-2">
                                <label class="form-label">Prezzo</label>
                            </div>

                            <div class="col-12 col-md-2">
                                <label class="form-label">Durata</label>
                            </div>

                            <div class="col-12 col-md-2">
                                <label class="form-label">Compenso</label>
                            </div>

                            <div class="col-12 col-md-1">
                                <label class="form-label">Attivo</label>
                            </div>
                        </div>

                        <div class="row text-white" v-for="service in doctor.services" :key="service.id">
                            <div class="col-12 col-md-4">
                                {{ service.name }}
                                <br>
                                {{ service.code }}
                            </div>

                            <div class="col-12 col-md-2">
                                {{ formatAmount(service.pivot.price) }} €
                            </div>

                            <div class="col-12 col-md-2">
                                {{ service.pivot.duration_minutes }} min
                            </div>

                            <div class="col-12 col-md-2">
                                <span v-if="service.pivot.compensation_type === 'percentage'">
                                    {{ formatAmount(service.pivot.compensation_value) }}%
                                </span>

                                <span v-else>
                                    {{ formatAmount(service.pivot.compensation_value) }} €
                                </span>
                            </div>

                            <div class="col-12 col-md-1">
                                <span class="circle" :class="service.pivot.active ? 'is-active' : 'not-active'">
                                    <i class="fas" :class="service.pivot.active ? 'fa-check' : 'fa-times'"></i>
                                </span>
                            </div>

                            <hr>
                        </div>
                    </div>

                </div>
                <div class="col-12">
                    <Calendar :appointments="doctor.appointments" :userCanCreateAppointment="userCanCreateAppointment"
                        :doctors="doctors" :nurses="nurses" :patients="patients" :doctor="doctor" />
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;

h3,
label {
    color: $mainRed;
}

.bg-main-red {
    padding: 20px;

    h3,
    label,
    hr {
        color: #fff;
    }
}

label {
    border-bottom: 1px solid $mainRed;
    font-weight: bold;
}

.border-right-red {
    border-right: 3px solid $mainRed;
}

.circle {
    padding: 10px;
    background-color: #fff;
    border-radius: 50%;

    &.is-active {
        color: green;
    }

    &.not-active {
        color: $mainRed;
    }
}

.table {
    margin-bottom: 0;
}

.table th,
.table td {
    vertical-align: middle;
}
</style>