import { ref, computed, watch } from "vue";

export const useTablePagination = (sortedItems) => {

    const perPage = ref(10);
    const currentPage = ref(1);
    const preventPageReset = ref(false);

    const totalPages = computed(() =>
        Object.keys(sortedItems.value).length === 0
            ? 1
            : Math.ceil(sortedItems.value.length / perPage.value)
    );

    const paginatedItems = computed(() => {

        if (Object.keys(sortedItems.value).length === 0) {
            return [];
        }

        const start = (currentPage.value - 1) * perPage.value;
        const end = start + perPage.value;

        return sortedItems.value.slice(start, end);

    });

    watch([perPage, sortedItems], () => {

        if (!preventPageReset.value) {
            currentPage.value = 1;
        }

        preventPageReset.value = false;

    });

    const watchItems = (getter) => {

        watch(getter, () => {

            if (!preventPageReset.value) {
                currentPage.value = totalPages.value || 1;
            }

            preventPageReset.value = false;

        });

    };

    return {
        perPage,
        currentPage,
        totalPages,
        paginatedItems,
        preventPageReset,
        watchItems,
    };

};
