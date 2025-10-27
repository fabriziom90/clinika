
export const sidebarMenus = {
    patients: [
        {
            id: "patients-section",
            title: "Gestione Pazienti",
            roles: ["superadmin", "doctor", "nurse"],
            icon: "fas fa-user-injured",
            links: [
                { name: "Lista Pazienti", route: "patients.index", icon: "fas fa-list" },
                {
                    name: "Nuovo Paziente",
                    route: "patients.create",
                    roles: ["superadmin", "doctor"],
                    icon: "fas fa-plus"
                },
            ],
        },
    ],
    doctors: [
        {
            id: "doctors-section",
            title: "Gestione Dottori",
            roles: ["superadmin"],
            icon: "fas fa-user-doctor",
            links: [
                { name: "Lista Dottori", route: "doctors.index", icon: "fas fa-list" },
                { name: "Aggiungi Dottore", route: "doctors.create", icon: "fas fa-plus" },
            ],
        },
    ],
    nurses: [
        {
            id: "nurses-section",
            title: "Gestione Infermieri",
            roles: ["superadmin"],
            icon: "fas fa-user-nurse",
            links: [
                { name: "Lista Infermieri", route: "nurses.index", icon: "fas fa-list" },
                { name: "Aggiungi Infermiere", route: "nurses.create", icon: "fas fa-plus" },
            ],
        },
    ],
    superadmin: [], 
};
