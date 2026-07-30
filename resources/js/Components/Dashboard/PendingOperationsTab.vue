<script setup>
import { Link } from '@inertiajs/vue3';
import CommonListTab from '../CommonListTab.vue';

defineProps({
    pendingOperations: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <CommonListTab title="Operazioni da svolgere">
        <div v-if="pendingOperations.length === 0" class="text-center py-4 text-muted">
            Nessuna operazione da svolgere
        </div>

        <ul v-else class="list-unstyled list-group list-group-flush">
            <li v-for="operation in pendingOperations" :key="operation.message" class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge me-2" :class="{
                            'bg-danger': operation.type === 'danger',
                            'bg-warning text-dark': operation.type === 'warning',
                            'bg-info': operation.type === 'info',
                        }">
                            {{ operation.count }}
                        </span>

                        {{ operation.message }}
                    </div>

                    <Link :href="operation.route" class="btn btn-sm btn-outline-primary">
                        Vai
                    </Link>
                </div>
            </li>
        </ul>
    </CommonListTab>
</template>

<style lang="scss" scoped>
@use '../../../scss/app.scss' as *;
</style>