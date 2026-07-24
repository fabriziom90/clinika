<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsentVersionRequest;
use App\Http\Requests\UpdateConsentVersionRequest;
use App\Models\ConsentType;
use App\Models\ConsentVersion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class ConsentVersionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ConsentType $consentType)
    {
        $consentVersions = ConsentVersion::with('consentType')->where('consent_type_id', $consentType->id)->orderBy('version', 'DESC')->get();

        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed all consent versions',
            'auditable_type' => 'App\Models\ConsentVersion',
            'auditable_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);

        return Inertia::render('ConsentVersions/IndexConsentVersions', [
            'consentType'     => $consentType,
            'consentVersions' => $consentVersions,
            'columns'   => [
                'id' => 'ID',
                'consent-type.name' => 'Tipologia',
                'version' => 'Versione',
                'is_active' => 'Attiva'
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ConsentType $consentType)
    {
        return Inertia::render('ConsentVersions/CreateConsentVersion', [
            'consentType' => $consentType
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsentVersionRequest $request, ConsentType $consentType)
    {
        $form_data = $request->validated();

        if ($form_data['is_active']) {
            $consentType->versions()
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $consentVersion = $consentType->versions()->create([
            'version' => ($consentType->versions()->max('version') ?? 0) + 1,
            'content' => $form_data['content'],
            'published_at' => now(),
            'is_active' => $form_data['is_active'],
        ]);

        app(\App\Observers\ConsentVersionObserver::class)->created($consentVersion);

        return redirect()
            ->route('admin.consent-types.consent-versions.index', $consentType)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Versione del consenso creata correttamente.',
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsentType $consentType, ConsentVersion $consentVersion)
    {
        app(\App\Observers\ConsentVersionObserver::class)->viewed($consentVersion);

        return Inertia::render('ConsentVersions/ShowConsentVersion', [
            'consentType' => $consentType,
            'consentVersion' => $consentVersion
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConsentVersion $consentVersion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsentVersionRequest $request, ConsentVersion $consentVersion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsentType $consentType, ConsentVersion $consentVersion)
    {
        if ($consentVersion->patientConsents()->exists()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Non è possibile eliminare una versione già utilizzata per un consenso.',
            ]);
        }

        $consentVersion->delete();

        app(\App\Observers\ConsentVersionObserver::class)->deleted($consentVersion);

        return redirect()
            ->route('admin.consent-types.consent-versions.index', $consentType)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Versione del consenso eliminata correttamente.',
            ]);
    }

    public function generatePdf(ConsentType $consentType, ConsentVersion $consentVersion) {
        abort_unless($consentVersion->consent_type_id === $consentType->id, 404);

        app(\App\Observers\ConsentVersionObserver::class)->viewed($consentVersion);

        $consentVersion = $consentVersion->load('consentType');

        $pdf = Pdf::loadView('pdf.consent-version',
            [
                'consentVersion' => $consentVersion,
            ]
        );

        return $pdf->stream(
            'consenso-' .Str::slug($consentVersion->consentType->name) .'-versione-' .$consentVersion->version .'.pdf'
        );
    }
}
