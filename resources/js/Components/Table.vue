<script setup>
import Modal from "./Modal.vue";
import { ref, computed, watch } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";

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
});

const emit = defineEmits(["updated"]);

// state
const perPage = ref(10);
const currentPage = ref(1);
const globalFilter = ref("");
const page = usePage();

// delete state
const deletingItem = ref(null);
const showDeleteModal = ref(false);
const preventPageReset = ref(false);

// filter by column
const columnFilters = ref({});

// count number of columns
const columnCount = computed(() => Object.keys(props.columns).length + 1);

// Editing inline
const editingItem = ref(null);
const editForms = ref({});

// global filtering + per column
const filteredItems = computed(() => {
    let filtered = props.items;

    if (globalFilter.value) {
        const g = globalFilter.value.toLowerCase();
        filtered = filtered.filter((item) =>
            Object.values(item).some((v) => String(v).toLowerCase().includes(g))
        );
    }

    Object.keys(columnFilters.value).forEach((key) => {
        const val = columnFilters.value[key];
        if (val) {
            filtered = filtered.filter((item) =>
                String(item[key]).toLowerCase().includes(val.toLowerCase())
            );
        }
    });

    return filtered;
});

// sorting
const sortColumn = ref(null);
const sortDirection = ref("asc");

function sortBy(column) {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortColumn.value = column;
        sortDirection.value = "asc";
    }
}

const sortedItems = computed(() => {
    if (!sortColumn.value) return filteredItems.value;
    return [...filteredItems.value].sort((a, b) => {
        const valA = a[sortColumn.value];
        const valB = b[sortColumn.value];

        if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
        if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;
        return 0;
    });
});

// pagination
const totalPages = computed(() =>
    Math.ceil(sortedItems.value.length / perPage.value)
);

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return sortedItems.value.slice(start, end);
});

watch([perPage, filteredItems], () => {
    if (!preventPageReset.value) {
        currentPage.value = 1;
    }
    preventPageReset.value = false;
});

watch(
    () => props.items.length,
    () => {
        if (!preventPageReset.value) {
            currentPage.value = totalPages.value || 1;
        }
        preventPageReset.value = false;
    }
);

// actions (show, edit, delete)
function showUrl(id) {
    return route(`${props.baseRoute}.show`, id);
}
function editUrl(id) {
    return route(`${props.baseRoute}.edit`, id);
}
function confirmDelete(id) {
    if (confirm("Vuoi davvero eliminare questo elemento?")) {
        // qui potresti emettere un evento al parent o usare Inertia.delete()
        alert(`Eliminato elemento con id ${id}`);
    }
}

// --- editing online (for specializations...)
function startEdit(item) {
    if (!props.editableColumns.length) return;
    editingItem.value = item.id;
    editForms.value[item.id] = {};
    props.editableColumns.forEach((col) => {
        editForms.value[item.id][col] = item[col];
    });
}

function cancelEdit() {
    editingItem.value = null;
}

function saveEdit(id) {
    const form = useForm({ ...editForms.value[id] });

    form.put(route(`${props.baseRoute}.update`, id), {
        preserveScroll: true,
        onSuccess: () => {
            form.editing = false;
            preventPageReset.value = true;
            emit("updated", page.props.specialties || page.props.items);
        },
        onError: (err) => {
            // eventuale gestione errori locali
            console.log(err);
        },
    });

    editingItem.value = null;
}

// delete functions
function openDeleteModal(item) {
    deletingItem.value = item;
    showDeleteModal.value = true;
}

function closeDeleteModal() {
    deletingItem.value = null;
    showDeleteModal.value = false;
}

function handleDeleted(updatedItems) {
    emit("updated", updatedItems);
}
</script>

<template>
    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th
                        v-for="(col, key) in columns"
                        :key="key"
                        @click="sortBy(key)"
                    >
                        {{ col }}
                    </th>
                    <th>Strumenti</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in paginatedItems" :key="item.id">
                    <td v-for="(col, key) in columns" :key="key">
                        <!-- Editing inline -->
                        <template
                            v-if="
                                editingItem === item.id &&
                                props.editableColumns.includes(key)
                            "
                        >
                            <input
                                v-model="editForms[item.id][key]"
                                class="form-control"
                            />
                        </template>
                        <template v-else>
                            {{ item[key] }}
                        </template>
                    </td>
                    <td class="actions">
                        <template
                            v-if="
                                props.editableColumns.length &&
                                editingItem !== item.id
                            "
                        >
                            <button
                                class="edit-button"
                                @click="startEdit(item)"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                        </template>

                        <template v-else-if="editingItem === item.id">
                            <button
                                class="save-edit-button"
                                @click="saveEdit(item.id)"
                            >
                                <i class="fas fa-check"></i>
                            </button>
                            <button
                                class="cancel-edit-button"
                                @click="cancelEdit"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </template>

                        <!-- Pulsanti standard per altre entità -->
                        <Link
                            v-if="!props.editableColumns.length"
                            class="show-button"
                            :href="showUrl(item.id)"
                        >
                            <i class="fas fa-eye"></i>
                        </Link>
                        <Link
                            v-if="!props.editableColumns.length"
                            class="edit-button"
                            :href="editUrl(item.id)"
                        >
                            <i class="fas fa-edit"></i>
                        </Link>

                        <button
                            class="delete-button"
                            @click="
                                () => {
                                    deletingItem = item;
                                    showDeleteModal = true;
                                }
                            "
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
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
            <button
                :disabled="currentPage === totalPages"
                @click="currentPage++"
            >
                <i class="fas fa-angle-right"></i>
            </button>
        </div>
    </div>
    <Modal
        :show="showDeleteModal"
        :item="deletingItem"
        :baseRoute="baseRoute"
        @close="closeDeleteModal"
        @deleted="handleDeleted"
    />
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

.custom-table {
    width: 100%;
    border-collapse: collapse;

    tr:nth-child(2n + 1) {
        background-color: #c5323771;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid $mainGrey;
    }

    td {
        vertical-align: middle;
    }

    th {
        background: $mainRed;
        color: #fff;
        cursor: pointer;
        vertical-align: top;
    }

    .column-filter {
        display: block;
        width: 100%;
        margin-top: 5px;
        font-size: 0.8em;
    }

    .actions {
        display: flex;
        gap: 8px;
        justify-content: center;

        button {
            cursor: pointer;
        }
    }
}

.pagination {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 10px;

    button {
        margin: 0 8px;
        @include button-link($mainRed, $mainRedHover, #fff);
    }
}
</style>
