export const sidebarMenus = {
    dashboard: [
        {
            id: "dashboard-section",
            title: "Dashboard",
            roles: ["superadmin", "doctor", "nurse", "secretary"],
            icon: "fas fa-home",
        },
    ],
    patients: [
        {
            id: "patients-section",
            title: "Gestione Pazienti",
            roles: ["superadmin", "doctor", "nurse", "secretary"],
            icon: "fas fa-user-injured",
            links: [
                {
                    name: "Lista Pazienti",
                    route: "admin.patients.index",
                    path: "/",
                    icon: "fas fa-list",
                },
                {
                    name: "Nuovo Paziente",
                    route: "admin.patients.create",
                    path: "/create",
                    roles: ["superadmin"],
                    icon: "fas fa-plus",
                },
            ],
        },
    ],
    doctors: [
        {
            id: "doctors-section",
            title: "Gestione Dottori",
            roles: ["superadmin", "secretary"],
            icon: "fas fa-user-doctor",
            links: [
                {
                    name: "Lista Dottori",
                    route: "admin.doctors.index",
                    path: "/",
                    icon: "fas fa-list",
                },
                {
                    name: "Aggiungi Dottore",
                    route: "admin.doctors.create",
                    path: "/create",
                    icon: "fas fa-plus",
                },
            ],
        },
    ],
    nurses: [
        {
            id: "nurses-section",
            title: "Gestione Infermieri",
            roles: ["superadmin", "secretary"],
            icon: "fas fa-user-nurse",
            links: [
                {
                    name: "Lista Infermieri",
                    route: "admin.nurses.index",
                    path: "/",
                    icon: "fas fa-list",
                },
                {
                    name: "Aggiungi Infermiere",
                    route: "admin.nurses.create",
                    path: "/create",
                    icon: "fas fa-plus",
                },
            ],
        },
    ],
    secretaries: [
        {
            id: "secretaries-section",
            title: "Gestione Segretarie",
            roles: ["superadmin", "secretary"],
            icon: "fas fa-hospital-user",
            links: [
                {
                    name: "Lista Segretarie",
                    route: "admin.secretaries.index",
                    path: "/",
                    icon: "fas fa-list",
                },
                {
                    name: "Aggiungi Segretarie",
                    route: "admin.secretaries.create",
                    path: "/create",
                    icon: "fas fa-plus",
                },
            ],
        },
    ],
    specialties: [
        {
            id: "specialties-section",
            title: "Gestione Specializzazioni",
            roles: ["superadmin", "secretary"],
            path: "/",
            icon: "fas fa-book-medical",
            links: [
                {
                    name: "Lista specializzazioni",
                    route: "admin.specialties.index",
                    path: "/",
                    icon: "fas fa-list",
                },
                {
                    name: "Aggiungi specializzazione",
                    route: "admin.specialties.create",
                    path: "/create",
                    icon: "fas fa-plus",
                },
            ],
        },
    ],
    roles: [
        {
            id: "roles-section",
            title: "Gestione Ruoli",
            roles: ["superadmin"],
            path: "/",
            icon: "fas fa-user-shield",
        },
    ],
    calendar: [
        {
            id: "calendar-section",
            title: "Gestione Agenda",
            roles: ["superadmin", "doctors", "nurses", "secretary"],
            path: "/",
            icon: "fas fa-calendar",
        },
    ],
    clinicrooms: [
        {
            id: "clinicrooms-section",
            title: "Gestione Stanze",
            roles: ["superadmin", "secretary"],
            path: "/",
            icon: "fas fa-hospital",
        },
    ],
    products: [
        {
            id: "products-section",
            title: "Gestione Prodotti medici",
            roles: ["superadmin", "secretary"],
            path: "/",
            icon: "fas fa-syringe",
        },
    ],
    drugs: [
        {
            id: "drugs-section",
            title: "Gestione Medicinali",
            roles: ["superadmin", "secretary"],
            path: "/",
            icon: "fas fa-pills",
        },
    ],
    logs: [
        {
            id: "logs-section",
            title: "Logs di sistema",
            roles: ["superadmin"],
            path: "/",
            icon: "fas fa-clipboard-list",
        },
    ],
    remindertypes: [
        {
            id: "reminder-types",
            title: "Tipologie di promemoria",
            roles: ["superadmin", "secretary"],
            path: "/",
            icon: "fas fa-alarm-clock",
            links: [
                {
                    name: "Lista tipologie",
                    route: "admin.reminder-types.index",
                    path: "/",
                    icon: "fas fa-list",
                },
                {
                    name: "Aggiungi tipologia",
                    route: "admin.reminder-types.create",
                    path: "/create",
                    icon: "fas fa-plus",
                },
            ],
        },
    ],
    reminders: [
        {
            id: "reminders",
            title: "Logs Promemoria",
            roles: ["superadmin", "secretary"],
            path: "/",
            icon: "fas fa-clipboard-list",
            links: [
                {
                    name: "Lista promemoria",
                    route: "admin.reminders.index",
                    path: "/",
                    icon: "fas fa-list",
                },
            ]
        },
    ],
    superadmin: [],
};
