<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Service;
use App\Models\Specialty;
use Inertia\Inertia;

class SpecialtyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Specialty::class, 'specialty');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialties = Specialty::all();

        return Inertia::render('Specialties/IndexSpecialties', ['specialties' => $specialties, 'columns' => ['id' => 'ID', 'name' => 'Nome']]);
    }

    public function show(Specialty $specialty)
    {
        return Inertia::render('Specialties/ShowSpecialty', ['specialty' => $specialty->load('services')]);
    }

    public function create()
    {
        $services = Service::all();

        return Inertia::render('Specialties/CreateSpecialty', ['services' => $services]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecialtyRequest $request)
    {
        $form_data = $request->validated();

        $services = $request->all()['service_ids'];

        $specialty = Specialty::create([
            'name' => $form_data['name'],
        ]);

        $specialty->services()->sync($services);

        // Ritorna alla pagina con i dati aggiornati e flash message
        return redirect()->route('admin.specialties.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Specializzazione aggiunta correttamente.',
            ]]);
    }

    public function edit(Specialty $specialty)
    {
        $services = Service::all();
        $specialty->load('services:id,name');

        return Inertia::render('Specialties/EditSpecialty', ['specialty' => $specialty, 'services' => Service::select('id', 'name')->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpecialtyRequest $request, Specialty $specialty)
    {
        $form_data = $request->validated();

        $services = $request->all()['service_ids'];

        $specialty->update($form_data);

        $specialty->services()->sync($request->service_ids);

        return redirect()->route('admin.specialties.index')->with(
            [
                'toast' => [
                    'type' => 'success',
                    'message' => 'Specializzazione modificata correttamente',
                ],
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialty $specialty)
    {
        $specialty->delete();

        return redirect()->route('admin.specialties.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Specializzazione cancellata con successo',
            ],
        ]);
    }
}
