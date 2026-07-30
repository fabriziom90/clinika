<script setup>
import { Link } from '@inertiajs/vue3';
import CommonListTab from '../CommonListTab.vue';
const props = defineProps({
    doctors: Array
})
</script>
<template>
    <CommonListTab title="Elenco dottori">
        <ul class="list-unstyled list-group list-group-flush">
            <li class="list-group-item" v-for="doctor in doctors" :key="doctor.id">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4>
                            <Link class="txt-main-red" :href="route('admin.doctors.show', doctor.id)">{{
                                doctor.name }} {{
                                    doctor.surname }}<i class="fas fa-arrow-right"></i></Link>
                        </h4>
                        <div><strong>{{ doctor.specialty }}</strong></div>
                    </div>
                    <div>
                        <h4>Appuntamenti</h4>
                        <ul class="list-unstyled">
                            <li class="list-item"><span class="dot bg-main-red"></span>
                                <span class="align-middle">Totale: {{ doctor.appointments_count }}</span>
                            </li>
                            <li class="list-item"><span class="dot bg-main-green"></span>
                                <span class="align-middle">Completati: {{ doctor.completed_count
                                }}</span>
                            </li>
                            <li class="list-item"><span class="dot bg-warning"></span>
                                <span class="align-middle">Rimanenti: {{ doctor.remaining_count
                                    }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </CommonListTab>

</template>


<style lang="scss" scoped>
@use '../../../scss/app.scss' as *;

.dot {
    display: inline-block;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    vertical-align: middle;
    margin-right: 10px;
}

.list-unstyled {
    li {
        margin: 10px 0px;
    }
}
</style>