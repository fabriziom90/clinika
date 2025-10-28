<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Specialty;
use Inertia\Inertia;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialties = Specialty::all();
        
        return Inertia::render('Specialties/IndexSpecialties', ['specialties' => $specialties, 'columns' => ['id' => 'ID', 'name' => 'Nome']]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecialtyRequest $request)
    {
        $form_data = $request->validated();
        
        $specialty = Specialty::create([
            'name' => $request->name,
        ]);

        // Ritorna alla pagina con i dati aggiornati e flash message
        return redirect()->route('admin.specialties.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Specializzazione aggiunta correttamente.',
            ]]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpecialtyRequest $request, Specialty $specialty)
    {
        $form_data = $request->validated();
        
        $specialty->update($form_data);

        return redirect()->route('admin.specialties.index')->with(
            [
                'toast' => [
                    'type' => 'success',
                    'message' => 'Specializzazione modificata correttamente'
                ]
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
                'message'   => 'Specializzazione cancellata con successo'
            ]
        ]);
    }
}
