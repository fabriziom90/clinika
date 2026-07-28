<script setup>
    import { Head } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { formatDate } from '@/utilities/formatDateFunction';

    const props = defineProps({
        reminder: Object
    })

    const statusLabel = (status) => {
        const labels = {
            pending: "In attesa",
            sent: "Inviato",
            failed: "Fallito"
        }

        return labels[status] ?? status;
    }

    const statusClass = (status) => {
        return {
            sent: "success",
            failed: "danger",
            pending: "warning"
        }[status];
    }
</script>
<template lang="">
    <Head title="Dettaglio promemoria" />
    <AuthenticatedLayout section="reminders">
        <div class="detail-wrapper">
            <div class="detail-card">
                <div class="detail-header">
                    <h2>Dettaglio promemoria</h2>
                    <span class="status-badge" :class="statusClass(reminder.status)">
                        {{ statusLabel(reminder.status) }}
                    </span>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Paziente</label>
                        <span>
                            {{ reminder.patient.name }}
                            {{ reminder.patient.surname }}
                        </span>
                    </div>
                    <div class="detail-item">
                        
                        <label>Tipologia</label>
                        <span>{{ reminder.reminder_type.name }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Appuntamento</label>
                        <span>{{ formatDate(reminder.appointment.start_time) }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Invio previsto</label>
                        <span>{{ formatDate(reminder.scheduled_for) }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Inviato il</label>
                        <span>{{reminder.sent_at ? formatDate(reminder.sent_at) : "-"}}
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Canale</label>

                        <span>{{ reminder.reminder_type.code }}</span>
                    </div>
                </div>
                <div class="message-section">
                    <label>Messaggio inviato</label>
                    <div class="message-box">{{ reminder.reminder_type.message }}</div>
                </div>
                <div v-if="reminder.error_message" class="error-section">
                    <label>Errore</label>
                    <div class="error-box">{{ reminder.error_message }}</div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
    @use "../../../scss/_partials/variables" as *;

    .detail-wrapper {
        width: 100%;
    }

    .detail-card {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
    }

    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;

        h2 {
            margin: 0;
        }
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;

        label,
        .message-section label,
        .error-section label {
            font-weight: 600;
            margin-bottom: 5px;
        }

        span {
            color: #555;
        }
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 14px;

        &.success {
            background: #d4edda;
            color: #155724;
        }

        &.danger {
            background: #f8d7da;
            color: #721c24;
        }

        &.warning {
            background: #fff3cd;
            color: #856404;
        }
    }

    .message-section,
    .error-section {
        margin-top: 30px;
    }

    .message-box,
    .error-box {
        margin-top: 10px;
        padding: 15px;
        border-radius: 8px;
        background: #f7f7f7;
        white-space: pre-line;
    }

    .error-box {
        background: #f8d7da;
        color: #721c24;
    }
</style>
