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
};
