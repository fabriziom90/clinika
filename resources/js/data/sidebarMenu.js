
export const sidebarMenus = {
    dashboard: [
        {
            id: "dashboard-section",
            title: "Dashboard",
            roles: ["superadmin", "doctor", 'nurse'],
            icon: "fas fa-home"
        }
    ],
    patients: [
        {
            id: "patients-section",
            title: "Gestione Pazienti",
            roles: ["superadmin", "doctor", "nurse"],
            icon: "fas fa-user-injured",
            links: [
                { name: "Lista Pazienti", route: "admin.patients.index", path:'/', icon: "fas fa-list" },
                {
                    name: "Nuovo Paziente",
                    route: "admin.patients.create",
                    path: '/create',
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
                { name: "Lista Dottori", route: "admin.doctors.index", path: '/', icon: "fas fa-list" },
                { name: "Aggiungi Dottore", route: "admin.doctors.create", path: '/create',icon: "fas fa-plus" },
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
                { name: "Lista Infermieri", route: "admin.nurses.index", path: '/', icon: "fas fa-list" },
                { name: "Aggiungi Infermiere", route: "admin.nurses.create", path: '/create', icon: "fas fa-plus" },
            ],
        },
    ],
    specialties: [
        {
            id: 'specialties-section',
            title: "Gestione Specializzazioni",
            roles: ["superadmin"],
            path: '/',
            icon: "fas fa-book-medical"
        }
    ],
    roles: [
        {
            id: 'roles-section',
            title: "Gestione Ruoli",
            roles: ["superadmin"],
            path: '/',
            icon: "fas fa-user-shield"
        }
    ],
    calendar: [
        {
            id: 'calendar-section',
            title: "Gestione Agenda",
            roles: ["superadmin", "doctors", "nurses"],
            path: '/',
            icon: "fas fa-calendar"
        }
    ],
    clinicrooms: [
        {
            id: 'clinicrooms-section',
            title: 'Gestione Stanze',
            roles: ["superadmin"],
            path: '/',
            icon: 'fas fa-hospital'
        }
    ],
    products: [
       {
            id: 'products-section',
            title: 'Gestione Prodotti medici',
            roles: ["superadmin"],
            path: '/',
            icon: 'fas fa-syringe'
        } 
    ],
    superadmin: [], 
};
