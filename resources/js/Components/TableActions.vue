<script setup>
import { Link, router } from "@inertiajs/vue3";
import { useConfigStore } from "@/stores/main";
import { tableConfig } from "@/config/tableConfig";

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },

    baseRoute: {
        type: String,
        required: true,
    },

    editableColumns: {
        type: Array,
        default: () => [],
    },

    editingItem: {
        type: [Number, String, null],
        default: null,
    }
});

const emit = defineEmits([
    "startEdit",
    "cancelEdit",
    "saveEdit",
    "delete",
    "generatePdf"
]);

const config = tableConfig[props.baseRoute] ?? {};
const actions = config.actions ?? {};

const { hasRole } = useConfigStore();

const getActionUrl = (action) => {
    const routeName = config.routes?.[action] ?? `${props.baseRoute}.${action}`;

    const params = config.routeParams?.[action]?.(props.item) ?? props.item.id;

    return route(routeName, params);
};

const sendResetEmail = () => {
    const routeName = `${props.baseRoute}.sendResetEmail`;

    router.post(
        route(routeName, props.item.id),
        {},
        {
            preserveScroll: true,
        }
    );
};

const generatePdf = () => {
    const routeName = config.routes?.generatePdf ?? `${props.baseRoute}.generate-pdf`;

    window.open(
        route(routeName, {
            consent_type: props.item.consent_type_id,
            consent_version: props.item.id,
        }),
        "_blank"
    );
};

</script>
<template>

    <td class="actions">
        <Link v-if="actions.show" class="show-button" :href="getActionUrl('show')">
            <i class="fas fa-eye"></i>
        </Link>
        <!-- Inline editing -->
        <template v-if="editableColumns.length && editingItem !== item.id">
            <button class="edit-button" @click="$emit('startEdit', item)">
                <i class="fas fa-edit"></i>
            </button>
        </template>

        <template v-else-if="editingItem === item.id">
            <button class="save-edit-button" @click="$emit('saveEdit', item.id)">
                <i class="fas fa-check"></i>
            </button>
            <button class="cancel-edit-button" @click="$emit('cancelEdit')">
                <i class="fas fa-times"></i>
            </button>
        </template>

        <Link v-if="actions.edit && !editableColumns.length && (hasRole('admin') || hasRole('secretary'))"
            class="edit-button" :href="getActionUrl('edit')">
            <i class="fas fa-edit"></i>
        </Link>

        <Link v-if="actions.versions" class="btn-blue"
            :href="route('admin.consent-types.consent-versions.index', item.id)" title="Gestisci versioni">
            <i class="fas fa-clock-rotate-left"></i>
        </Link>

        <button v-if="actions.generatePdf" class="btn-blue" @click="generatePdf" title="Genera PDF">
            <i class="fas fa-file-pdf"></i>
        </button>

        <Link v-if="actions.showConsenses" class="btn-dark" :href="route('admin.patient.consents.index', item.id)"
            title="Consensi paziente">
            <i class="fas fa-user-shield"></i>
        </Link>

        <button v-if="actions.resetEmail" class="btn-blue" @click="sendResetEmail">
            <i class="fas fa-envelope"></i>
        </button>

        <button v-if="hasRole('admin') || hasRole('secretary')" class="delete-button" @click="$emit('delete', item)">
            <i class="fas fa-trash"></i>
        </button>
    </td>

</template>
