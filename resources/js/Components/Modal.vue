<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    item: Object,
    baseRoute: String,
    parentId: {
        type: [Number, String],
        default: null
    }
});

const emit = defineEmits(["close", "deleted"]);

const confirmDelete = () => {
    if (!props.item) return;

    let routeParams;

    if (props.baseRoute === "admin.consent-types.consent-versions") {
        routeParams = {
            consent_type: props.parentId,
            consent_version: props.item.id,
        };
    } else if (props.baseRoute === "admin.patient.consents") {
        routeParams = {
            patient: props.parentId,
            consent: props.item.id,
        };
    } else {
        routeParams = props.item.id;
    }

    router.delete(route(`${props.baseRoute}.destroy`, routeParams), {
        preserveScroll: true,
        onSuccess: (page) => {
            emit("deleted", page.props.items || page.props.specialties);
            emit("close");
        },
        onError: (err) => {
            console.error(err);
        },
    });
};

const displayName = (item) => {
    if (!item) return "";

    // case patient, nurses, doctors
    if (item.name && item.surname) {
        return `${item.name} ${item.surname}`;
    }

    // caso only name
    if (item.name) {
        return item.name;
    }

    // case product or drugs
    if (item.product?.name || item.drug?.name) {
        return item.product ? item.product.name : item.drug.name;
    }

    // fallback: mostra eventuale titolo, email, ecc.
    if (item.title) return item.title;
    if (item.email) return item.email;

    // altrimenti solo ID
    return `#${item.id}`;
};
</script>

<template>
    <div v-if="show" class="modal fade show modal-bg" style="display: block">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferma eliminazione</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <p>Vuoi davvero eliminare "{{ displayName(item) }}"?</p>
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
</style>
