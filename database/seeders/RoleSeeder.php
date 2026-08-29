<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // === ENTITÀ DELL'APPLICAZIONE ===
        $entities = [
            'patient' => 'Paziente',
            'doctor' => 'Medico',
            'secretary' => 'Segretaria',
            'nurse' => 'Infermiere',
            'appointment' => 'Appuntamento',
            'user' => 'Utente di sistema',
            'role' => 'Ruolo',
            'specialty' => 'Specializzazioni',
            'service' => 'Prestazione sanitaria',
            'clinic-room' => 'Stanze Poliambulatorio',
            'product' => 'Prodotti sanitari',
            'drug' => 'Medicinali',
            'inventory-drug' => 'Medicinali stanza',
            'inventory-product' => 'Prodotti stanza',
            'medical-record' => 'Cartella clinica',
            'medical-entry' => 'Voce cartella clinica',
            'medical-attachment' => 'Allegato clinico',
            'prescription' => 'Prescrizione',
            'vital-parameter' => 'Parametri vitali',
            'invoices' => 'Fatture',
            'patient-health-history' => 'Anamnesi Paziente',
            'reminder-type' => 'Tipologia promemoria',
            'consent-type' => 'Tipologia consensi',
            'consent-version' => 'Versione tipologia consensi',
            'patient-consent' => 'Consensi paziente',
        ];

        // === AZIONI CRUD ===
        $actions = [
            'create' => 'Crea',
            'view' => 'Visualizza',
            'update' => 'Modifica',
            'delete' => 'Elimina',
        ];

        // === CREAZIONE PERMESSI ===
        foreach ($entities as $entityKey => $entityLabel) {
            foreach ($actions as $actionKey => $actionLabel) {
                Permission::updateOrCreate(
                    [
                        'name' => "{$entityKey}.{$actionKey}",
                        'guard_name' => $guard,
                    ],
                    [
                        'display_name' => "{$actionLabel} {$entityLabel}",
                    ]
                );
            }
        }

        // === PERMESSI EXTRA ===
        $extraPermissions = [
            [
                'name' => 'invoices.change-status',
                'display_name' => 'Cambia stato fattura',
            ],
            [
                'name' => 'appointment.change-status',
                'display_name' => 'Cambia stato appuntamento',
            ],
        ];

        foreach ($extraPermissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => $guard,
                ],
                [
                    'display_name' => $permission['display_name'],
                ]
            );
        }

        // === CREAZIONE RUOLI ===
        $roles = [
            ['name' => 'admin', 'display_name' => 'Admin'],
            ['name' => 'doctor', 'display_name' => 'Medico'],
            ['name' => 'nurse', 'display_name' => 'Infermiere'],
            ['name' => 'secretary', 'display_name' => 'Segretaria'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                [
                    'name' => $role['name'],
                    'guard_name' => $guard,
                ],
                [
                    'display_name' => $role['display_name'],
                ]
            );
        }

        // === RECUPERO RUOLI ===
        $admin = Role::findByName('admin', $guard);
        $doctor = Role::findByName('doctor', $guard);
        $nurse = Role::findByName('nurse', $guard);
        $secretary = Role::findByName('secretary', $guard);

        // === AMMINISTRATORE ===
        $admin->syncPermissions(
            Permission::where('guard_name', $guard)->get()
        );

        // === MEDICO ===
        $doctor->syncPermissions([
            'patient.view',
            'appointment.view',
            'appointment.create',
            'appointment.update',
            'medical-record.view',
            'medical-entry.view',
            'medical-entry.create',
            'medical-entry.update',
            'medical-attachment.create',
            'medical-attachment.view',
            'prescription.create',
            'prescription.view',
            'vital-parameter.create',
            'vital-parameter.view',
            'patient-health-history.create',
            'patient-health-history.view',
            'patient-health-history.update',
        ]);

        // === SEGRETARIA ===
        $allPermissions = Permission::where('guard_name', $guard)
            ->pluck('name')
            ->toArray();

        $excludedPermissions = [
            'medical-record.create',
            'medical-record.update',
            'medical-record.delete',

            'medical-entry.create',
            'medical-entry.update',
            'medical-entry.delete',

            'medical-attachment.create',
            'medical-attachment.update',
            'medical-attachment.delete',

            'vital-parameter.create',
            'vital-parameter.update',
            'vital-parameter.delete',

            'prescription.create',
            'prescription.update',
            'prescription.delete',

            'patient-health-history.create',
            'patient-health-history.update',
            'patient-health-history.delete',

            'audit-logs.view',
            'audit-logs.create',
            'audit-logs.update',
            'audit-logs.delete',
        ];

        $secretaryPermissions = array_values(
            array_diff($allPermissions, $excludedPermissions)
        );

        $secretary->syncPermissions($secretaryPermissions);

        // === INFERMIERE ===
        $nurse->syncPermissions([
            'patient.view',
            'appointment.view',
            'appointment.create',
            'appointment.update',
            'medical-record.view',
            'medical-entry.view',
            'medical-attachment.view',
            'prescription.view',
            'vital-parameter.view',
            'patient-health-history.view',
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
