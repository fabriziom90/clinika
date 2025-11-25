<?php

namespace App\Http\Controllers\Admin;

use App\Models\ClinicRoom;
use App\Models\InventoryProduct;
use App\Models\InventoryDrug;
use App\Models\Product;
use App\Models\Drug;
use App\Http\Requests\StoreClinicRoomRequest;
use App\Http\Requests\UpdateClinicRoomRequest;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ClinicRoomController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\ClinicRoom::class, 'clinic_room');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clinicRooms = ClinicRoom::all();
        return Inertia::render('ClinicRooms/IndexClinicRooms', [ 'clinicRooms' => $clinicRooms, 'columns' => [
                'id'    => 'ID',
                'name'  => 'Nome',
            ]]);
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
    public function store(StoreClinicRoomRequest $request)
    {
        $form_data = $request->validated();

        $newClinicRoom = new ClinicRoom();
        $newClinicRoom->name = $form_data['name'];
        $newClinicRoom->save();

        return redirect()->route('admin.clinic-rooms.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => "Stanza creata con successo."
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClinicRoom $clinicRoom)
    {   
        $clinicRoomProducts = InventoryProduct::where('room_id', $clinicRoom->id)->with('product')->get();
        $clinicRoomDrugs = InventoryDrug::where('room_id', $clinicRoom->id)->with('drug')->get();
        $products = Product::all();
        $drugs = Drug::all();
        

        return Inertia::render('ClinicRooms/ShowClinicRoom', [
            'clinicRoom' => $clinicRoom,
            'clinicRoomProducts' => $clinicRoomProducts,
            'clinicRoomDrugs'   => $clinicRoomDrugs,
            'products'  => $products,
            'drugs'     => $drugs,
            'columns'   => [
                'id'    => 'ID',
                'item_name' => 'Nome',
                'expiry_date' => 'Data Scadenza',
                'units' => 'Quantità',
                'price' => 'Prezzo'
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClinicRoom $clinicRoom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClinicRoomRequest $request, ClinicRoom $clinicRoom)
    {
        $form_data = $request->validated();

        $clinicRoom->name = $form_data['name'];
        $clinicRoom->save();

        return redirect()->route('admin.clinic-rooms.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => "Stanza modificata con successo."
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicRoom $clinicRoom)
    {
        $clinicRoom->delete();

        return redirect()->route('admin.clinic-rooms.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => "Stanza cancellata con successo."
            ]
        ]);
    }
}
