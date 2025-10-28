import { watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

export function useGlobalToast() {
    const page = usePage();
    const $toast = useToast();
    console.log(page);
    watch(
        () => page.props.toast,
        (toast) => {
            if (!toast) return;
            if (toast.type === "success") {
                $toast.success(toast.message, { position: "top-right", duration: 3000 });
            } else if (toast.type === "error") {
                $toast.error(toast.message, { position: "top-right", duration: 3000 });
            }
        },
        { immediate: true }
    );
}
