<script setup>
import { Link } from '@inertiajs/vue3';
import CommonListTab from '../CommonListTab.vue';
import { formatDate } from '@/utilities/formatDateFunction.js';

const props = defineProps({
    patients: Array
})
</script>
<template>
    <CommonListTab title="Elenco pazienti">
        <ul class="list-unstyled list-group list-group-flush">
            <li class="list-group-item" v-for="patient in patients" :key="patient.id">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4>
                            <Link class="txt-main-red" :href="route('admin.patients.show', patient.id)">
                                {{ patient.name }} {{ patient.surname }}<i class="fas fa-arrow-right"></i>
                            </Link>
                        </h4>
                    </div>
                    <div class="text-end">
                        <div>
                            <strong>Ultima visita:</strong>
                            {{ patient.last_appointment ? formatDate(patient.last_appointment.start_time) : 'Nessuna' }}
                        </div>
                        <div>

                            <strong>Ultima visita:</strong>
                            {{ patient.next_appointment ? formatDate(patient.next_appointment.start_time) : 'Nessuna' }}
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </CommonListTab>
</template>


<style></style>