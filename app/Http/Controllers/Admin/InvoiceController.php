<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeInvoiceStatusRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Invoice::class, 'invoice');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::query()
            ->select([
                'id',
                'uuid',
                'number',
                'date',
                'full_name',
                'amount',
                'status',
                'patient_id',
                'doctor_id',
                'description',
                'created_at',
            ])
            ->with([
                'patient:id,name,surname',
                'doctor:id,user_id',
                'doctor.user:id,name,surname',
                'invoiceItems:id,invoice_id,service_id,description,quantity,unit_price,total',
                'invoiceItems.service:id,name',
            ])
            ->orderByDesc('date')
            ->paginate(15);

        Audit::forceCreate([
            'user_id' => auth()->id(),
            'user_type' => get_class(auth()->user()),
            'event' => 'viewed invoices',
            'auditable_type' => 'App\Models\Invoice',
            'auditable_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);

        return Inertia::render('Invoices/IndexInvoice', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Appointment $appointment)
    {
        $appointment->load([
            'doctor.user',
            'doctor.services',
            'patient',
            'service',
        ]);

        $doctorService = $appointment->doctor
            ->services()
            ->where('services.id', $appointment->service_id)
            ->first();

        $price = $doctorService->pivot->price;

        Audit::forceCreate([
            'user_id' => auth()->id(),
            'user_type' => get_class(auth()->user()),
            'event' => 'show invoice form creation',
            'auditable_type' => 'App\Models\Invoice',
            'auditable_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);

        $doctorService = $appointment->doctor->services->firstWhere('id', $appointment->service_id);

        $price = $doctorService?->pivot?->price ?? 0;

        return Inertia::render('Invoices/CreateInvoice', [
            'appointment' => $appointment,
            'services' => $appointment->doctor->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->pivot->price,
                ];
            }),
            'invoice' => [
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id,
                'date' => now()->format('Y-m-d'),
                'full_name' => trim(
                    $appointment->patient->name.' '.
                    $appointment->patient->surname
                ),
                'vat_number' => $appointment->patient->personal_code,
                'address' => $appointment->patient->address,
                'city' => $appointment->patient->city,
                'zip_code' => $appointment->patient->zip_code,
                'description' => $appointment->service->name,
                'vat_amount' => 0,
                'discount_amount' => 0,
                'stamp_duty' => 0,
                'amount' => $price,
                'subtotal' => $price,
                'total' => $price,
                'payment_method' => '',
                'items' => [
                    [
                        'service_id' => $appointment->service_id,
                        'description' => $appointment->service->name,
                        'quantity' => 1,
                        'unit_price' => $price,
                        'vat_percentage' => 0,
                        'total' => $price,
                    ],
                ],
            ],

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        DB::beginTransaction();

        $alreadyExists = Invoice::where('appointment_id', $request->appointment_id)->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'appointment' => 'Esiste già una fattura per questo appuntamento',
            ]);
        }

        try {

            $year = now()->year;

            $lastInvoice = Invoice::where('year', $year)
                ->orderByDesc('progressive_number')
                ->first();

            $progressiveNumber = $lastInvoice
                ? $lastInvoice->progressive_number + 1
                : 1;

            $subtotal = collect($request->items)
                ->sum(fn ($item) => $item['quantity'] * $item['unit_price']
                );

            $vatAmount = collect($request->items)
                ->sum(function ($item) {
                    return (
                        $item['quantity']
                        * $item['unit_price']
                        * $item['vat_percentage']
                    ) / 100;
                });
            $total = $subtotal + $vatAmount + $request->stamp_duty - $request->discount_amount;
            $amount = $total;

            $invoice = Invoice::create([
                'uuid' => Str::uuid(),

                'appointment_id' => $request->appointment_id,
                'doctor_id' => $request->doctor_id,
                'patient_id' => $request->patient_id,

                'number' => sprintf('%s/%s', $progressiveNumber, $year),
                'progressive_number' => $progressiveNumber,
                'year' => $year,

                'date' => $request->date,

                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'stamp_duty' => $request->stamp_duty,
                'discount_amount' => $request->discount_amount,
                'total' => $total,
                'amount' => $amount,

                'status' => 'draft',
                'payment_method' => $request->payment_method,

                'full_name' => $request->full_name,
                'vat_number' => $request->vat_number,
                'address' => $request->address,
                'city' => $request->city,
                'zip_code' => $request->zip_code,

                'description' => $request->description,

                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $item) {

                $invoice->invoiceItems()->create([
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'vat_percentage' => $item['vat_percentage'],
                    'total' => $item['total'],
                ]);
            }

            Audit::forceCreate([
                'user_id' => auth()->id(),
                'user_type' => get_class(auth()->user()),
                'event' => 'invoice created',
                'auditable_type' => Invoice::class,
                'auditable_id' => $invoice->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => [],
                'new_values' => $invoice->toArray(),
            ]);

            DB::commit();

            return redirect()->route('admin.invoices.index')->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Fattura creata correttamente.',
                ]]);

        } catch (\Throwable $e) {

            DB::rollBack();

            // report($e);

            return back()
                ->withErrors([
                    'error' => 'Si è verificato un errore durante la creazione della fattura.',
                ])
                ->withInput();
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $path = $pdfService->getPdfPath($invoice);

        return response()->file(storage_path('app/'.$path));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load([
            'invoiceItems.service',
            'patient',
            'doctor.user',
            'doctor.services',
        ]);

        $services = $invoice->doctor
            ? $invoice->doctor
                ->services()
                ->select('services.id', 'services.name', 'doctor_service.price')
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->pivot->price,
                    ];
                })
                ->values()
            : collect();

        Audit::forceCreate([
            'user_id' => auth()->id(),
            'user_type' => get_class(auth()->user()),
            'event' => 'invoice created',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $invoice->toArray(),
        ]);

        return Inertia::render('Invoices/EditInvoice', [
            'invoice' => [
                'id' => $invoice->id,
                'uuid' => $invoice->uuid,

                'appointment_id' => $invoice->appointment_id,
                'doctor_id' => $invoice->doctor_id,
                'patient_id' => $invoice->patient_id,

                'date' => $invoice->date,

                'full_name' => $invoice->full_name,
                'vat_number' => $invoice->vat_number,
                'address' => $invoice->address,
                'city' => $invoice->city,
                'zip_code' => $invoice->zip_code,

                'description' => $invoice->description,

                'subtotal' => $invoice->subtotal,
                'vat_amount' => $invoice->vat_amount,
                'stamp_duty' => $invoice->stamp_duty,
                'discount_amount' => $invoice->discount_amount,
                'total' => $invoice->total,
                'amount' => $invoice->amount,

                'status' => $invoice->status,
                'payment_method' => $invoice->payment_method,

                'items' => $invoice->invoiceItems->map(fn ($item) => [
                    'id' => $item->id,
                    'service_id' => $item->service_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'vat_percentage' => $item->vat_percentage,
                    'total' => $item->total,
                ]),
            ],

            'services' => $services,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        DB::beginTransaction();

        try {
            $subtotal = collect($request->items)
                ->sum(fn ($item) => $item['quantity'] * $item['unit_price']
                );

            $vatAmount = collect($request->items)
                ->sum(fn ($item) => (
                    $item['quantity']
                    * $item['unit_price']
                    * $item['vat_percentage']
                ) / 100
                );

            $discount = $subtotal * (
                $request->discount_amount / 100
            );

            $total =
                $subtotal +
                $vatAmount +
                $request->stamp_duty -
                $discount;

            $invoice->update([
                'date' => $request->date,

                'full_name' => $request->full_name,
                'vat_number' => $request->vat_number,
                'address' => $request->address,
                'city' => $request->city,
                'zip_code' => $request->zip_code,

                'description' => $request->description,

                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'stamp_duty' => $request->stamp_duty,
                'discount_amount' => $request->discount_amount,
                'total' => $total,
                'amount' => $total,

                'payment_method' => $request->payment_method,
            ]);

            $invoice->invoiceItems()->delete();

            foreach ($request->items as $item) {

                $invoice->invoiceItems()->create([
                    'service_id' => $item['service_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'vat_percentage' => $item['vat_percentage'],
                    'total' => $item['total'],
                ]);
            }

            Audit::forceCreate([
                'user_id' => auth()->id(),
                'user_type' => get_class(auth()->user()),
                'event' => 'invoice updated',
                'auditable_type' => Invoice::class,
                'auditable_id' => $invoice->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => [],
                'new_values' => $invoice->fresh()->toArray(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.invoices.index')
                ->with([
                    'toast' => [
                        'type' => 'success',
                        'message' => 'Fattura aggiornata correttamente.',
                    ],
                ]);
        } catch (\Throwable $e) {
            DB::rollback();

            return back()->withErrors([
                'error' => 'Errore durante il salvataggio della fattura',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        Audit::forceCreate([
            'user_id' => auth()->id(),
            'user_type' => get_class(auth()->user()),
            'event' => 'invoice deleted',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $invoice->toArray(),
            'new_values' => [],
        ]);

        return redirect()->route('admin.invoices.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Fattura cancellata correttamente',
            ],
        ]);
    }

    public function changeStatus(ChangeInvoiceStatusRequest $request, Invoice $invoice, InvoicePdfService $pdfService)
    {

        $allowedTransitions = [
            'draft' => ['issued', 'cancelled'],
            'issued' => ['paid', 'cancelled'],
            'paid' => [],
            'cancelled' => [],
        ];

        $currentStatus = $invoice->status;
        $newStatus = $request->status;

        if (! in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return back()->withErrors([
                'status' => 'Cambio di stato non consentito',
            ]);
        }

        if ($newStatus === 'issued') {
            $path = $pdfService->getPdfPath($invoice);
        }

        $oldStatus = $invoice->status;

        $invoice->update(['status' => $newStatus]);

        Audit::forceCreate([
            'user_id' => auth()->id(),
            'user_type' => get_class(auth()->user()),
            'event' => 'invoice status changed',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $oldStatus,
            'new_values' => [
                'status' => $newStatus,
            ],
        ]);

        return back();
    }
}
