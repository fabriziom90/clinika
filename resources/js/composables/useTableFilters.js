import { ref, computed } from "vue";

export const useTableFilters = (items) => {

    const globalFilter = ref("");

    const columnFilters = ref({});

    const filteredItems = computed(() => {

        let filtered = items.value;

        if (globalFilter.value) {

            const g = globalFilter.value.toLowerCase();

            filtered = filtered.filter(item =>
                Object.values(item).some(value =>
                    String(value).toLowerCase().includes(g)
                )
            );

        }

        Object.keys(columnFilters.value).forEach(key => {

            const value = columnFilters.value[key];

            if (!value) {
                return;
            }

            filtered = filtered.filter(item =>
                String(item[key])
                    .toLowerCase()
                    .includes(value.toLowerCase())
            );

        });

        return filtered;

    });

    return {
        globalFilter,
        columnFilters,
        filteredItems,
    };

};
