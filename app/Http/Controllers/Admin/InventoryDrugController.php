<?php

namespace App\Http\Controllers\Admin;

use App\Models\InventoryDrug;
use App\Http\Requests\StoreInventoryDrugRequest;
use App\Http\Requests\UpdateInventoryDrugRequest;
use App\Http\Controllers\Controller;

class InventoryDrugController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreInventoryDrugRequest $request)
    {   
        
        $form_data = $request->validated();
        
        $checkDrug = InventoryDrug::where('drug_id', $form_data['drug_id'])->get();
        
        if(count($checkDrug) > 0){
            return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $form_data['room_id']])->with([
                'toast' => [
                    'type' => 'error',
                    'message' => 'E\' già presente questo prodotto nella stanza.'
                ]
            ]);
        }
        
        $newInventoryDrug = new InventoryDrug();
        $newInventoryDrug->room_id = $form_data['room_id'];
        $newInventoryDrug->drug_id = $form_data['drug_id'];
        $newInventoryDrug->expiry_date = $form_data['expiry_date'];
        $newInventoryDrug->units = $form_data['units'];

        $newInventoryDrug->save();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $form_data['room_id']])->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Medicinale salvato con successo all\'interno della stanza.'
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryDrug $inventoryDrug)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryDrug $inventoryDrug)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryDrugRequest $request, InventoryDrug $inventoryDrug)
    {   
        $form_data = $request->validated();

        $inventoryDrug->expiry_date = $form_data['expiry_date'];
        $inventoryDrug->units = $form_data['units'];

        $inventoryDrug->save();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $inventoryDrug->room_id])->with([
            'toast' => [
                'type' => 'success',
                'message' => "Medicinale modificato correttamente."
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryDrug $inventoryDrug)
    {
        $inventoryDrug->delete();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $inventoryDrug->room_id])->with([
            'toast' => [
                'type' => 'success',
                'message' => "Medicinale cancellato correttamente."
            ]
        ]);
    }
}
