import { ref, computed } from "vue";

export const useTableSorting = (filteredItems) => {

    const sortColumn = ref(null);
    const sortDirection = ref("asc");

    const sortBy = (column) => {

        if (sortColumn.value === column) {
            sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        } else {
            sortColumn.value = column;
            sortDirection.value = "asc";
        }

    };

    const sortedItems = computed(() => {

        if (!sortColumn.value) {
            return filteredItems.value;
        }

        return [...filteredItems.value].sort((a, b) => {

            const valA = a[sortColumn.value];
            const valB = b[sortColumn.value];

            if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
            if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;

            return 0;
        });

    });

    return {
        sortColumn,
        sortDirection,
        sortBy,
        sortedItems,
    };

};
