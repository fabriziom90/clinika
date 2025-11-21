<?php

namespace App\Http\Controllers\Admin;

use App\Models\Drug;
use App\Http\Requests\StoreDrugRequest;
use App\Http\Requests\UpdateDrugRequest;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DrugController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drugs = Drug::all();
        return Inertia::render('Drugs/IndexDrugs', [
            'drugs' => $drugs,
            'columns' => [
                'id' => 'ID',
                'name' => 'Nome',
                'unit_price' => 'Prezzo unitario'
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDrugRequest $request)
    {
        $form_data = $request->validated();

        $newDrug = new Drug();
        $newDrug->name = $form_data['name'];
        $newDrug->unit_price = $form_data['unit_price'];

        $newDrug->save();

        return redirect()->route('admin.drugs.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Medicinale creato con successo'
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Drug $drug)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drug $drug)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDrugRequest $request, Drug $drug)
    {
        $form_data = $request->validated();

        
        $drug->name = $form_data['name'];
        $drug->unit_price = $form_data['unit_price'];

        $drug->save();

        return redirect()->route('admin.drugs.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Medicinale modificato con successo'
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drug $drug)
    {   
        $drug->delete();

        return redirect()->route('admin.drugs.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Medicinale cancellato con successo'
            ]
        ]);
    }
}
