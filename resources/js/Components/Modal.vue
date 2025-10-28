<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    item: Object,
    baseRoute: String,
});

const emit = defineEmits(["close", "deleted"]);

function confirmDelete() {
    if (!props.item) return;

    router.delete(route(`${props.baseRoute}.destroy`, props.item.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            emit("deleted", page.props.items || page.props.specialties);
            emit("close");
        },
        onError: (err) => {
            console.error(err);
        },
    });
}
</script>

<template>
    <div v-if="show" class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferma eliminazione</h5>
                    <button
                        type="button"
                        class="btn-close"
                        @click="$emit('close')"
                    ></button>
                </div>
                <div class="modal-body">
                    <p>Vuoi davvero eliminare "{{ item?.name }}"?</p>
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
<style lang="scss" scoped>
@use "../../scss/app.scss";
@use "../../scss/_partials/variables" as *;

.modal-bg {
    background-color: $mainBgTransparent;

    .modal-header {
        background-color: $mainRed;
        color: #fff;
    }
}
</style>
