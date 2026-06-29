<script setup>
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toast-notification';

const props = defineProps({
    show: Boolean,
    invoice: Object
})

const emit = defineEmits(['close', 'deleted']);

const $toast = useToast();

const confirmDelete = () => {
    router.delete(route("admin.invoices.destroy", props.invoice.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            emit("deleted", page.props.invoices);
            emit("close");
        },
        onError: (errors) => {
            $toast.error(Object.values(errors).flat().join("\n"));
        }

    })
}
</script>
<template lang="">
    <div v-if="show" class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferma eliminazione</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <p>Vuoi davvero eliminare la fattura numero "{{ invoice.number }}"?</p>
                </div>
                <div class="modal-footer">
                    <button class="secondary-button" @click="$emit('close')">
                        Annulla
                    </button>
                    <button class="main-button" @click="confirmDelete">
                        Elimina
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="">

</style>
