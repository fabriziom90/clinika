<script setup>
import CommonListTab from '../CommonListTab.vue';
import { formatDate } from '@/utilities/formatDateFunction.js';
const props = defineProps({
    title: String,
    recentActivities: Array
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
</script>
<template>
    <CommonListTab title="Attività recenti">
        <ul class="list-unstyled list-group list-group-flush">
            <li v-for="activity in recentActivities" :key="activity.id" class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div>
                            {{ activity.message }}
                        </div>
                    </div>

                    <div class="text-muted">
                        {{ formatDate(activity.created_at) }} {{ getTime(activity.created_at) }}
                    </div>
                </div>
            </li>
        </ul>
    </CommonListTab>
</template>


<style></style>