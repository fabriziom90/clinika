<?php

namespace App\Http\Controllers\Admin;

use App\Models\ClinicRoom;
use App\Http\Requests\StoreClinicRoomRequest;
use App\Http\Requests\UpdateClinicRoomRequest;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ClinicRoomController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\ClinicRoom::class, 'clinic-room');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clinicRooms = ClinicRoom::all();
        return Inertia::render('ClinicRooms/IndexClinicRoom', [ 'clinicRooms' => $clinicRooms]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ClinicRoom $clinicRoom)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicRoom $clinicRoom)
    {
        //
    }
}
