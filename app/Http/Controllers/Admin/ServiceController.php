<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Service::class, 'service');
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
    public function store(StoreServiceRequest $request)
    {
        $services = $request->validated()['services'];

        $createdServices = [];

        foreach ($services as $service) {

            $prefix = 'SRV';
            $code = $this->serviceCodeFromName($service['name']);
            $date = now()->format('ym');

            $counter = Service::where('code', 'like', "{$prefix}-{$code}-{$date}%")->lockForUpdate()->count() + 1;

            $code = sprintf(
                '%s-%s-%s-%02d',
                $prefix,
                $code,
                $date,
                $counter
            );

            $service['code'] = $code;

            $newService = new Service;
            $newService->fill($service);
            $newService->save();

            $createdServices[] = $newService->fresh();
        }

        return redirect()->back()->with([
            'service' => $createdServices,
            'toast' => [
                'type' => 'success',
                'message' => 'Prestazione/i sanitaria/e inserita/e con successo',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());

        return redirect()->back()->with([
            'service' => $service->fresh(),
            'toast' => [
                'type' => 'success',
                'message' => 'Prestazione sanitaria modificata con successo',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->specialties()->detach();
        $service->doctors()->detach();

        $service->delete();

        return redirect()->back()->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Prestazione sanitaria cancellata con successo',
            ],
        ]);
    }

    public function serviceCodeFromName(string $name): string
    {
        return strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
    }
}
