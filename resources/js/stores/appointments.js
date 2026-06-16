import { defineStore } from "pinia";

export const useAppointmentStore = defineStore("appointments", {
    state: () => ({
        items: [],
        selectedId: null,
    }),
    getters: {
        selected: (state) =>
            state.items.find(
                (appointment) => appointment.id === state.selectedId,
            ),
    },
    actions: {
        remove(id) {
            this.items = this.items.filter(
                (appointment) => appointment.id !== id,
            );
        },
        setAppointments(appointments) {
            this.items = appointments;
        },
        select(id) {
            this.selectedId = id;
        },
        updateStatus(id, status, label) {
            const appointment = this.items.find((a) => a.id === id);
            if (!appointment) return;
            appointment.status = status;
            appointment.status_label = label;
        },
    },
});
