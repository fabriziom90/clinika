export const tableConfig = {
    "admin.clinic-rooms": {
        actions: {
            show: true,
            edit: true,
        },
    },

    "admin.doctors": {
        actions: {
            show: true,
            edit: true,
            resetEmail: true,
        },
    },

    "admin.patients": {
        actions: {
            show: true,
            edit: true,
            resetEmail: true,
            showConsenses: true
        },
    },

    "admin.nurses": {
        actions: {
            show: true,
            edit: true,
            resetEmail: true,
        },
    },

    "admin.secretaries": {
        actions: {
            show: true,
            edit: true,
            resetEmail: true,
        },
    },

    "admin.consent-types": {
        actions: {
            show: true,
            edit: true,
            versions: true,
        },
    },

    "admin.consent-types.consent-versions": {
        actions: {
            show: true,
            edit: false,
            generatePdf: true
        },

        routes: {
            show: "admin.consent-types.consent-versions.show",
        },

        routeParams: {
            show: (item) => ({
                consent_type: item.consent_type_id,
                consent_version: item.id,
            }),
        },
    },
    "admin.patient.consents": {
        actions: {
            show: false,
            edit: true,
        },

        routes:{
            edit: "admin.patient.consents.edit"
        },

        routeParams: {
            edit: (item) => ({
                patient: item.patient_id,
                consent: item.id
            })
        }
    },
    "admin.specialties": {
        actions: {
            show: false,
            edit: true,
            delete: true,
        },
        routes: {
            edit: "admin.specialties.edit",
        },
    },
};
