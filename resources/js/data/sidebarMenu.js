
export const sidebarMenus = {
    patients: [
        {
            id: "patients-section",
            title: "Gestione Pazienti",
            roles: ["superadmin", "doctor", "nurse"],
            links: [
                { name: "Lista Pazienti", route: "patients.index" },
                {
                    name: "Nuovo Paziente",
                    route: "patients.create",
                    roles: ["superadmin", "doctor"],
                },
            ],
        },
    ],
    doctors: [
        {
            id: "doctors-section",
            title: "Gestione Dottori",
            roles: ["superadmin"],
            links: [
                { name: "Lista Dottori", route: "doctors.index" },
                { name: "Aggiungi Dottore", route: "doctors.create" },
            ],
        },
    ],
    nurses: [
        {
            id: "nurses-section",
            title: "Gestione Infermieri",
            roles: ["superadmin"],
            links: [
                { name: "Lista Infermieri", route: "nurses.index" },
                { name: "Aggiungi Infermiere", route: "nurses.create" },
            ],
        },
    ],
    superadmin: [], 
};
