<script setup>
import Modal from "./Modal.vue";
import { ref, computed, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { formatTableValue } from "../utilities/tableFormatter.js";
import TableActions from "./TableActions.vue";
import { useTableSorting } from "@/composables/useTableSorting.js";
import { useTablePagination } from "@/composables/useTablePagination.js";
import { useTableFilters } from "@/composables/useTableFilters.js";

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
    columns: {
        type: Object,
        required: true,
    },
    baseRoute: {
        type: String,
        required: true,
    },
    editableColumns: {
        type: Array,
        default: () => [], // es. ['name'] per le specializzazioni
    },
    parentId: {
        type: [Number, String],
        default: null
    }
});

//emit defining
const emit = defineEmits(["updated"]);

//page
const page = usePage();

// delete state
const deletingItem = ref(null);
const showDeleteModal = ref(false);

// count number of columns
const columnCount = computed(() => Object.keys(props.columns).length + 1);

// Editing inline
const editingItem = ref(null);
const editForms = ref({});

//column filtering
const items = computed(() => props.items);
const { globalFilter, columnFilters, filteredItems } = useTableFilters(items);

//sorting
const { sortColumn, sortDirection, sortBy, sortedItems } = useTableSorting(filteredItems);

//pagination
const {
    perPage,
    currentPage,
    totalPages,
    paginatedItems,
    preventPageReset,
    watchItems,
} = useTablePagination(sortedItems);

watchItems(() => props.items.length);

// actions (show, edit, delete)
const showUrl = (id) => {
    return route(`${props.baseRoute}.show`, id);
};
const editUrl = (id) => {
    return route(`${props.baseRoute}.edit`, id);
};

// --- editing inline (for specializations...)
const startEdit = (item) => {
    if (!props.editableColumns.length) return;
    editingItem.value = item.id;
    editForms.value[item.id] = {};
    props.editableColumns.forEach((col) => {
        editForms.value[item.id][col] = item[col];
    });
};

const cancelEdit = () => {
    editingItem.value = null;
};

const saveEdit = (id) => {
    const form = useForm({ ...editForms.value[id] });

    form.put(route(`${props.baseRoute}.update`, id), {
        preserveScroll: true,
        onSuccess: () => {
            form.editing = false;
            preventPageReset.value = true;
            emit(
                "updated",
                page.props.specialties ||
                page.props.clinicRooms ||
                page.props.inventoryProducts ||
                page.props.products ||
                page.props.items
            );
        },
        onError: (err) => {
            // eventuale gestione errori locali
            console.log(err);
        },
    });

    editingItem.value = null;
};

const closeDeleteModal = () => {
    deletingItem.value = null;
    showDeleteModal.value = false;
};

const handleDeleted = (updatedItems) => {
    emit("updated", updatedItems);
};

function normalizeValueForInput(value, key) {
    if (!value) return "";

    // Se è una data nel formato DD/MM/YYYY → converte in YYYY-MM-DD
    if (key.toLowerCase().includes("date")) {
        // se è già ISO lo ritorna
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }

        // se è nel formato DD/MM/YYYY lo converte
        const parts = value.split("/");
        if (parts.length === 3) {
            const [day, month, year] = parts;
            return `${year}-${month}-${day}`;
        }
    }

    return value;
}
</script>

<template>
    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th v-for="(col, key) in columns" :key="key" @click="sortBy(key)">
                        {{ col }}
                        <i v-if="sortColumn === key" :class="[
                            'fa',
                            sortDirection === 'asc'
                                ? 'fa-sort-up'
                                : 'fa-sort-down',
                        ]" class="ms-1"></i>
                    </th>
                    <th>Strumenti</th>
                </tr>

                <!-- 🔽 Riga filtri per colonna -->
                <tr>
                    <th v-for="(col, key) in columns" :key="key">
                        <input v-model="columnFilters[key]" type="text" class="form-control column-filter"
                            placeholder="Filtra..." />
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in paginatedItems" :key="item.id">
                    <td v-for="(col, key) in columns" :key="key">
                        <!-- Editing inline -->
                        <template v-if="
                            editingItem === item.id &&
                            props.editableColumns.includes(key)
                        ">
                            <input v-if="key.toLowerCase().includes('date')" type="date" class="form-control" :value="normalizeValueForInput(
                                editForms[item.id][key],
                                key
                            )
                                " @input="
                                    editForms[item.id][key] =
                                    $event.target.value
                                    " />

                            <input v-else v-model="editForms[item.id][key]" class="form-control" />
                        </template>
                        <template v-else>
                            <div v-if="key === 'name' && item.active != undefined" class="status-dot"
                                :class="item.active == true ? 'active' : 'inactive'"></div>
                            {{ formatTableValue(item, key) }}
                        </template>
                    </td>
                    <TableActions
                        :item="item"
                        :baseRoute="baseRoute"
                        :editableColumns="editableColumns"
                        :editingItem="editingItem"

                        @startEdit="startEdit"
                        @cancelEdit="cancelEdit"
                        @saveEdit="saveEdit"
                        @delete="(item) => {
                            deletingItem = item;
                            showDeleteModal = true;
                        }"
                    />
                </tr>
                <tr v-if="!paginatedItems.length">
                    <td :colspan="columnCount" class="text-center">
                        Nessun risultato
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Paginazione -->
        <div class="pagination">
            <button :disabled="currentPage === 1" @click="currentPage--">
                <i class="fas fa-angle-left"></i>
            </button>
            <span>Pagina {{ currentPage }} di {{ totalPages }}</span>
            <button :disabled="currentPage === totalPages" @click="currentPage++">
                <i class="fas fa-angle-right"></i>
            </button>
        </div>
    </div>
    <Modal :show="showDeleteModal" :item="deletingItem" :baseRoute="baseRoute" :parentId="parentId" @close="closeDeleteModal"
        @deleted="handleDeleted" />
</template>

<style lang="scss" scoped>
@use "../../scss/_partials/mixins" as *;
@use "../../scss/_partials/variables" as *;
@use "../../scss/app.scss";

.table-wrapper {
    width: 100%;
}

select {
    background-color: $mainRed;
    border: none;
    padding: 5px 10px 3px;
    color: #fff;
}

.table-controls {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;

    .global-filter {
        padding: 5px;
    }
}

.status-dot {
    display: inline-block;
    vertical-align: middle;
    width: 25px;
    height: 25px;
    border-radius: 50%;

    &.active {
        background-color: #28a745;
    }

    &.inactive {
        background-color: #dc3545;
    }
}
</style>
