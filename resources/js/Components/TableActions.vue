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

const config = tableConfig[props.baseRoute] ?? {};

const emit = defineEmits([
    "startEdit",
    "cancelEdit",
    "saveEdit",
    "delete",
]);

const { hasRole } = useConfigStore();

const showUrl = (id) => {
    return route(`${props.baseRoute}.show`, id);
};

const editUrl = (id) => {
    return route(`${props.baseRoute}.edit`, id);
};

const sendResetEmail = (item) => {
    const routeName = `${props.baseRoute}.sendResetEmail`;
    router.post(
        route(routeName, item.id),
        {},
        {
            preserveScroll: true,
        }
    );
};

</script>
<template>

    <td class="actions">
        <!-- Show solo per clinic rooms -->
        <template v-if="config.showButton">
            <Link class="show-button" :href="showUrl(item.id)" >
                <i class="fas fa-eye"></i>
            </Link>
        </template>

        <!-- Edit inline -->
        <template v-if=" props.editableColumns.length && editingItem !== item.id ">
            <button class="edit-button" @click="$emit('startEdit', item)" >
                <i class="fas fa-edit"></i>
            </button>
        </template>

        <template v-else-if="editingItem === item.id" >
            <button class="save-edit-button" @click="$emit('saveEdit', item.id)" >
                <i class="fas fa-check"></i>
            </button>
            <button class="cancel-edit-button" @click="$emit('cancelEdit')">
                <i class="fas fa-times"></i>
            </button>
        </template>

        <!-- Show standard -->
        <Link v-if="!props.editableColumns.length" class="show-button" :href="showUrl(item.id)" >
            <i class="fas fa-eye"></i>
        </Link>
        <!-- Edit standard -->
        <Link v-if="!props.editableColumns.length && (hasRole('superadmin') || hasRole('secretary'))" class="edit-button" :href="editUrl(item.id)">
            <i class="fas fa-edit"></i>
        </Link>

        <!-- Delete -->
        <button v-if="hasRole('superadmin') || hasRole('secretary')" class="delete-button" @click="$emit('delete', item)">
            <i class="fas fa-trash"></i>
        </button>
        <!-- Reset email utenti -->
        <button v-if="config.resetEmail" class="btn-blue" @click="sendResetEmail(item)">
            <i class="fas fa-envelope"></i>
        </button>
    </td>

</template>
