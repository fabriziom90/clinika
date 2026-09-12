// resources/js/directives/loading.js

import { router } from "@inertiajs/vue3";

let activeButton = null;

router.on("start", () => {
    if (!activeButton) {
        return;
    }

    activeButton.disabled = true;

    const label = activeButton.dataset.loadingText || "Caricamento...";

    activeButton.innerHTML = `
        ${label}
        <i class="fa-solid fa-spinner fa-spin ms-2"></i>
    `;
});

router.on("finish", () => {
    if (!activeButton) {
        return;
    }

    activeButton.disabled = false;

    const label = activeButton.dataset.originalText;

    if (label) {
        activeButton.innerHTML = label;
    }

    activeButton = null;
});

export default {
    mounted(el) {
        if (el.tagName !== "BUTTON") {
            return;
        }

        el.dataset.originalText = el.innerHTML;

        el.addEventListener("click", () => {
            activeButton = el;
        });
    },
};