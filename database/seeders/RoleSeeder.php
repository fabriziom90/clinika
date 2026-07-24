<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $superadmin = Role::create(['name' => 'superadmin']);
        // $doctor = Role::create(['name' => 'doctor']);
        // $nurse = Role::create(['name' => 'nurse']);

        // // Permessi base
        // Permission::create(['name' => 'manage users']);
        // Permission::create(['name' => 'manage patients']);
        // Permission::create(['name' => 'manage appointments']);
        // Permission::create(['name' => 'manage invoices']);

        // // Assegno permessi ai ruoli
        // $superadmin->givePermissionTo(Permission::all());
        // $doctor->givePermissionTo(['manage patients', 'manage appointments']);
        // $nurse->givePermissionTo(['manage patients']);

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
            'inventory-product' => 'Prodotti stanza',
            'medical-record' => 'Cartella clinica',
            'medical-entry' => 'Voce cartella clinica',
            'medical-attachment' => 'Allegato clinico',
            'prescription' => 'Prescrizione',
            'vital-parameter' => 'Parametri vitali',
            'invoices' => 'Fatture',
            'patient-health-history' => 'Anamnesi Paziente',
            'reminder-type' => 'Tipologia promemoria',
            'consent-type'  => 'Tipologia consensi',
            'consent-version' => 'Versione tipologia consensi'
        ];

        // === AZIONI CRUD ===
        $actions = [
            'create' => 'Crea',
            'view' => 'Visualizza',
            'update' => 'Modifica',
            'delete' => 'Elimina',
        ];

        // === CREAZIONE PERMESSI ===
        $permissions = [];

        foreach ($entities as $entityKey => $entityLabel) {
            foreach ($actions as $actionKey => $actionLabel) {
                $permissions[] = [
                    'name' => "{$entityKey}.{$actionKey}",
                    'display_name' => "{$actionLabel} {$entityLabel}",
                ];
            }
        }

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                ['display_name' => $perm['display_name']]
            );
        }

        // === CREAZIONE RUOLI ===
        $roles = [
            ['name' => 'superadmin', 'display_name' => 'Superadmin'],
            ['name' => 'doctor', 'display_name' => 'Medico'],
            ['name' => 'nurse', 'display_name' => 'Infermiere'],
            ['name' => 'secretary', 'display_name' => 'Segretaria'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name']]
            );
        }

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
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }

        // === ASSEGNAZIONE PERMESSI AI RUOLI ===
        $superadmin = Role::findByName('superadmin');
        $doctor = Role::findByName('doctor');
        $nurse = Role::findByName('nurse');
        $secretary = Role::findByName('secretary');

        // Superamministratore
        $superadmin->syncPermissions(Permission::all());

        // Medico
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

        // Segretaria
        $allPermissions = Permission::pluck('name')->toArray();

        // permessi da escludere
        $excludedPermissions = Permission::whereIn('name', [
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
        ])->pluck('name')->toArray();

        // differenza
        $permissions = array_values(array_diff($allPermissions, $excludedPermissions));

        $secretary->syncPermissions($permissions);

        // Infermiere
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

    }
}
