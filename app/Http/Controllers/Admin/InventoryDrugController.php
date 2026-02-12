<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryDrugRequest;
use App\Http\Requests\UpdateInventoryDrugRequest;
use App\Models\InventoryDrug;
use Illuminate\Http\Request;

class InventoryDrugController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\InventoryDrug::class, 'inventoryDrug');
    }

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

        if (count($checkDrug) > 0) {
            return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $form_data['room_id']])->with([
                'toast' => [
                    'type' => 'error',
                    'message' => 'E\' già presente questo prodotto nella stanza.',
                ],
            ]);
        }

        $newInventoryDrug = new InventoryDrug;
        $newInventoryDrug->room_id = $form_data['room_id'];
        $newInventoryDrug->drug_id = $form_data['drug_id'];
        $newInventoryDrug->expiry_date = $form_data['expiry_date'];
        $newInventoryDrug->units = $form_data['units'];

        $newInventoryDrug->save();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $form_data['room_id']])->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Medicinale salvato con successo all\'interno della stanza.',
            ],
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
                'message' => 'Medicinale modificato correttamente.',
            ],
        ]);
    }

    public function updateQuantity(Request $request, InventoryDrug $inventoryDrug)
    {

        $form_data = $request->validate(
            [
                'quantity' => ['required'],
            ],
            [
                'quantity.required' => 'La data di scadenza è obbligatoria.',
                'quantity.date' => 'La data di scadenza deve essere una data valida.',
            ]
        );

        $inventoryDrug->units = $form_data['quantity'];

        $inventoryDrug->update();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $inventoryDrug->room_id])->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Medicinale modificato correttamente.',
            ],
        ]);
    }

    public function updateExpiryDate(Request $request, InventoryDrug $inventoryDrug)
    {

        $form_data = $request->validate(
            [
                'expirationDate' => ['required', 'date'],
            ],
            [
                'expirationDate.required' => 'La data di scadenza è obbligatoria.',
                'expirationDate.date' => 'La data di scadenza deve essere una data valida.',
            ]
        );

        $inventoryDrug->expiry_date = $form_data['expirationDate'];

        $inventoryDrug->update();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $inventoryDrug->room_id])->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Scadenza aggiornata correttamente.',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryDrug $inventoryDrug)
    {
        $clinicRoom = $inventoryDrug->room;

        $inventoryDrug->delete();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $clinicRoom])->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Medicinale cancellato correttamente.',
            ],
        ]);
    }
}
