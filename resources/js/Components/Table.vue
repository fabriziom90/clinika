<script setup>
import { ref, computed, watch } from "vue";
import { Link } from "@inertiajs/vue3";

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
});

// Stato
const perPage = ref(10);
const currentPage = ref(1);
const globalFilter = ref("");

// Filtri per colonna
const columnFilters = ref({});

// Filtro globale + per colonna
const filteredItems = computed(() => {
    let filtered = props.items;

    // filtro globale
    if (globalFilter.value) {
        const g = globalFilter.value.toLowerCase();
        filtered = filtered.filter((item) =>
            Object.values(item).some((v) => String(v).toLowerCase().includes(g))
        );
    }

    // filtro per colonna
    Object.keys(columnFilters.value).forEach((key) => {
        console.log(columnFilters);
        const val = columnFilters.value[key];
        if (val) {
            filtered = filtered.filter((item) =>
                String(item[key]).toLowerCase().includes(val.toLowerCase())
            );
        }
    });

    return filtered;
});

// Ordinamento
const sortColumn = ref(null);
const sortDirection = ref("asc");

function sortBy(column) {
    console.log(column);
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

// Paginazione
const totalPages = computed(() =>
    Math.ceil(sortedItems.value.length / perPage.value)
);

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return sortedItems.value.slice(start, end);
});

watch([perPage, filteredItems], () => {
    currentPage.value = 1;
});

watch(
    () => props.items.length,
    () => {
        currentPage.value = totalPages.value || 1;
    }
);

// Azioni (edit, show, delete)
function showUrl(id) {
    return route(`${props.baseRoute}.show`, id);
}
function editUrl(id) {
    return route(`${props.baseRoute}.edit`, id);
}
function confirmDelete(id) {
    if (confirm("Vuoi davvero eliminare questo elemento?")) {
        // qui potresti emettere un evento per il parent
        // o chiamare una route di eliminazione via Inertia.delete()
        alert(`Eliminato elemento con id ${id}`);
    }
}
</script>

<template>
    <div class="table-wrapper">
        <div class="table-controls">
            <label>
                Mostra
                <select v-model.number="perPage">
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
                risultati
            </label>

            <input
                v-model="globalFilter"
                type="text"
                placeholder="Filtra..."
                class="global-filter"
            />
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th
                        v-for="(col, index) in columns"
                        :key="col"
                        @click="sortBy(index)"
                    >
                        {{ col }}
                        <span v-if="sortColumn === index">
                            {{
                                sortDirection === "asc" ? "&#9650;" : "&#9660;"
                            }}
                        </span>
                        <input
                            v-model="columnFilters[index]"
                            placeholder="Filtra"
                            class="column-filter"
                        />
                    </th>
                    <th>Strumenti</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in paginatedItems" :key="item.id">
                    <td v-for="(col, key) in columns" :key="col">
                        {{ item[key] }}
                    </td>
                    <td class="actions">
                        <Link class="show-button" :href="showUrl(item.id)"
                            ><i class="fas fa-eye"></i
                        ></Link>
                        <Link class="edit-button" :href="editUrl(item.id)"
                            ><i class="fas fa-edit"></i
                        ></Link>
                        <button
                            class="delete-button"
                            @click="confirmDelete(item.id)"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr v-if="!paginatedItems.length">
                    <td :colspan="columns.length + 1" class="text-center">
                        Nessun risultato
                    </td>
                </tr>
            </tbody>
        </table>

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
