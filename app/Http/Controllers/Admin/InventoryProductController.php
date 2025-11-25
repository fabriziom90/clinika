<?php

namespace App\Http\Controllers\Admin;

use App\Models\InventoryProduct;
use App\Http\Requests\StoreInventoryProductRequest;
use App\Http\Requests\UpdateInventoryProductRequest;
use App\Http\Controllers\Controller;

class InventoryProductController extends Controller
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
    public function store(StoreInventoryProductRequest $request)
    {
        $form_data = $request->validated();
        
        $checkProduct = InventoryProduct::where('product_id', $form_data['product_id'])->get();
        
        if(count($checkProduct) > 0){
            return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $form_data['room_id']])->with([
                'toast' => [
                    'type' => 'error',
                    'message' => 'E\' già presente questo prodotto nella stanza.'
                ]
            ]);
        }
        
        $newInventoryProduct = new InventoryProduct();
        $newInventoryProduct->room_id = $form_data['room_id'];
        $newInventoryProduct->product_id = $form_data['product_id'];
        $newInventoryProduct->expiry_date = $form_data['expiry_date'];
        $newInventoryProduct->units = $form_data['units'];

        $newInventoryProduct->save();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $form_data['room_id']])->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Prodotto salvato con successo all\'interno della stanza.'
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryProduct $inventoryProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryProduct $inventoryProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryProductRequest $request, InventoryProduct $inventoryProduct)
    {   
        $form_data = $request->validated();

        $inventoryProduct->expiry_date = $form_data['expiry_date'];
        $inventoryProduct->units = $form_data['units'];

        $inventoryProduct->save();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $inventoryProduct->room_id])->with([
            'toast' => [
                'type' => 'success',
                'message' => "Prodotto modificato correttamente."
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryProduct $inventoryProduct)
    {
        $inventoryProduct->delete();

        return redirect()->route('admin.clinic-rooms.show', ['clinic_room' => $inventoryProduct->room_id])->with([
            'toast' => [
                'type' => 'success',
                'message' => "Prodotto cancellato correttamente."
            ]
        ]);
    }
}
