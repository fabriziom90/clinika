<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\InventoryDrug;
use App\Models\InventoryProduct;
use App\Models\Invoice;
use App\Models\MedicalEntry;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\PatientConsent;
use App\Models\Product;
use OwenIt\Auditing\Models\Audit;

class AuditActivityFormatter
{
    public function format(Audit $audit): ?array
    {
        $user = $audit->user
            ? $audit->user->name.' '.$audit->user->surname
            : 'Sistema';

        $message = match ([$audit->auditable_type, $audit->event]) {
            [Patient::class, 'created'] => "{$user} ha registrato un nuovo paziente",

            [Patient::class, 'updated'] => "{$user} ha modificato i dati di un paziente",

            [Patient::class, 'deleted'] => "{$user} ha eliminato un paziente",

            [Appointment::class, 'created'] => "{$user} ha registrato un nuovo appuntamento",

            [Appointment::class, 'updated'] => "{$user} ha modificato un appuntamento",

            [Appointment::class, 'deleted'] => "{$user} ha eliminato un appuntamento",

            [Appointment::class, 'status changed'] => "{$user} ha modificato lo stato di un appuntamento",

            [Invoice::class, 'created'] => "{$user} ha emesso una nuova fattura",

            [Invoice::class, 'updated'] => "{$user} ha modificato una fattura",

            [Invoice::class, 'deleted'] => "{$user} ha eliminato una fattura",

            [Invoice::class, 'status changed'] => "{$user} ha modificato lo stato di una fattura",

            [Invoice::class, 'viewed'] => "{$user} ha visualizzato una fattura",

            [PatientConsent::class, 'created'] => "{$user} ha registrato un nuovo consenso",

            [PatientConsent::class, 'updated'] => "{$user} ha modificato un consenso",

            [PatientConsent::class, 'deleted'] => "{$user} ha eliminato un consenso",

            [PatientConsent::class, 'viewed'] => "{$user} ha visualizzato un documento di consenso",

            [MedicalEntry::class, 'created'] => "{$user} ha registrato un nuovo referto",

            [MedicalEntry::class, 'updated'] => "{$user} ha modificato un referto",

            [MedicalEntry::class, 'deleted'] => "{$user} ha eliminato un referto",

            [Doctor::class, 'created'] => "{$user} ha registrato un nuovo medico",

            [Doctor::class, 'updated'] => "{$user} ha modificato i dati di un medico",

            [Doctor::class, 'deleted'] => "{$user} ha eliminato un medico",

            [Nurse::class, 'created'] => "{$user} ha registrato un nuovo infermiere",

            [Nurse::class, 'updated'] => "{$user} ha modificato i dati di un infermiere",

            [Nurse::class, 'deleted'] => "{$user} ha eliminato un infermiere",

            [Drug::class, 'created'] => "{$user} ha inserito un nuovo farmaco",

            [Drug::class, 'updated'] => "{$user} ha modificato un farmaco",

            [Drug::class, 'deleted'] => "{$user} ha eliminato un farmaco",

            [Product::class, 'created'] => "{$user} ha inserito un nuovo prodotto",

            [Product::class, 'updated'] => "{$user} ha modificato un prodotto",

            [Product::class, 'deleted'] => "{$user} ha eliminato un prodotto",

            [InventoryProduct::class, 'created'] => "{$user} ha registrato un prodotto in inventario",

            [InventoryProduct::class, 'updated'] => "{$user} ha modificato un prodotto in inventario",

            [InventoryProduct::class, 'deleted'] => "{$user} ha eliminato un prodotto dall'inventario",

            [InventoryDrug::class, 'created'] => "{$user} ha registrato un farmaco in inventario",

            [InventoryDrug::class, 'updated'] => "{$user} ha modificato un farmaco in inventario",

            [InventoryDrug::class, 'deleted'] => "{$user} ha eliminato un farmaco dall'inventario",

            default => null,
        };

        if (! $message) {
            return null;
        }

        return [
            'id' => $audit->id,
            'message' => $message,
            'created_at' => $audit->created_at,
        ];
    }
}
