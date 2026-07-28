<script setup>
import { ref, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toast-notification';

const props = defineProps({
    show: Boolean,
    invoice: Object
});

const emit = defineEmits([
    'close', 'updated'
]);

const selectedStatus = ref("");
const $toast = useToast();


watch(
    () => props.invoice,
    (invoice) => {
        selectedStatus.value = invoice?.status ?? ""
    },
    { immediate: true }
)


const changeStatus = (invoice, status) => {
    router.put(route('admin.invoices.change-status', props.invoice.uuid),
        { status: selectedStatus.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit("updated", {
                    id: props.invoice.id,
                    status: selectedStatus.value
                });

                emit("close");


                $toast.success("Stato della fattura aggiornato correttamente")
            },
            onError: (errors) => {
                $toast.error(
                    Object.values(errors).flat().join("\n"));

            }
        }
    )
}

</script>
<template>
    <div v-if="show" class="modal fade show modal-bg" style="display:block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Cambia stato fattura
                    </h5>

                    <button type="button" class="btn-close" @click="$emit('close')" />
                </div>

                <div class="modal-body">

                    <p>
                        Fattura:
                        <strong>
                            {{ invoice?.number }}
                        </strong>
                    </p>

                    <select class="form-select" v-model="selectedStatus">
                        <option value="draft">
                            Bozza
                        </option>

                        <option value="issued">
                            Emessa
                        </option>

                        <option value="paid">
                            Pagata
                        </option>

                        <option value="cancelled">
                            Annullata
                        </option>
                    </select>

                </div>

                <div class="modal-footer">

                    <button class="secondary-button" @click="$emit('close')">
                        Annulla
                    </button>

                    <button class="main-button" @click="changeStatus">
                        Salva
                    </button>

                </div>

            </div>
        </div>
    </div>
</template>
<style lang="scss"></style>
