<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsentTypeRequest;
use App\Http\Requests\UpdateConsentTypeRequest;
use App\Models\ConsentType;
use Inertia\Inertia;

class ConsentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('ConsentTypes/IndexConsentTypes',
            [
                'consentTypes' => ConsentType::all(),
                'columns' => [
                    'id'  => 'ID',
                    'code'  => 'Codice',
                    'name'  => 'Nome',
                    'acquisition_method' => 'Metodo acquisizione',
                    'is_required' => 'Obbligatorio',
                    'is_active' => 'Attivo'
                ]
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ConsentTypes/CreateConsentType');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsentTypeRequest $request)
    {
        $form_data = $request->validated();
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsentType $consentType)
    {
        return Inertia::render('ConsentTypes/ShowConsentType', ['consentType' => $consentType]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConsentType $consentType)
    {
        return Inertia::render('ConsentTypes/EditConsentType', ['consentType' => $consentType]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsentTypeRequest $request, ConsentType $consentType)
    {
        $form_data = $request->validated();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsentType $consentType)
    {
        //
    }
}
