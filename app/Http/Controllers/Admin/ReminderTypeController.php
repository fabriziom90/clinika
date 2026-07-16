<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReminderTypeRequest;
use App\Http\Requests\UpdateReminderTypeRequest;
use App\Models\ReminderType;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReminderTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reminders = ReminderType::all();

        return Inertia::render('ReminderTypes/IndexReminderTypes', [
            'reminders' => $reminders,
            'columns' => [
                'id' => 'ID',
                'name' => 'Nome',
                'code' => 'Codice',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ReminderTypes/CreateReminderType');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReminderTypeRequest $request)
    {
        $form_data = $request->validated();

        ReminderType::create([
            'name' => $form_data['name'],
            'code' => Str::slug($form_data['name']),
            'subject' => $form_data['subject'],
            'message' => $form_data['message'],
            'sent_before_value' => $form_data['sent_before_value'],
            'sent_before_unit' => $form_data['sent_before_unit'],
            'active' => $form_data['active'],
        ]);

        return redirect()->route('admin.reminder-types.index')->with(
            [
                'toast' => [
                    'type' => 'success',
                    'message' => 'Tipologia promemoria aggiunta correttamente',
                ],
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ReminderType $reminderType)
    {
        return Inertia::render('ReminderTypes/ShowReminderType', [
            'reminderType' => $reminderType,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReminderType $reminderType)
    {
        return Inertia::render('ReminderTypes/EditReminderType', ['reminderType' => $reminderType]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReminderTypeRequest $request, ReminderType $reminderType)
    {
        $form_data = $request->validated();

        $reminderType->update([
            'name' => $form_data['name'],
            'code' => Str::slug($form_data['name']),
            'subject' => $form_data['subject'] ?? null,
            'message' => $form_data['message'],
            'sent_before_value' => $form_data['sent_before_value'],
            'sent_before_unit' => $form_data['sent_before_unit'],
            'active' => $form_data['active'],
        ]);

        return redirect()->route('admin.reminder-types.index')->with(
            [
                'toast' => [
                    'type' => 'success',
                    'message' => 'Tipologia promemoria modificata correttamente',
                ],
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReminderType $reminderType)
    {
        $reminderType->delete();

        return redirect()->route('admin.reminder-types.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Tipologia promemoria cancellata correttamente',
            ],
        ]);
    }
}
