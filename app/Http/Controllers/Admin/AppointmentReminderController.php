<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentReminder;
use App\Models\ReminderType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentReminderController extends Controller
{
    public function index(Request $request)
    {
        $query = AppointmentReminder::with([
            'appointment',
            'patient',
            'reminderType',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reminder_type_id')) {
            $query->where('reminder_type_id', $request->reminder_type_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_for', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_for', '<=', $request->date_to);
        }

        $reminders = $query
            ->orderByDesc('scheduled_for')
            ->get();

        /**
         * Filtro paziente su dati cifrati.
         * Viene fatto dopo il recupero perché il database
         * non può effettuare LIKE su valori criptati.
         */
        if ($request->filled('patient')) {

            $search = strtolower($request->patient);

            $reminders = $reminders->filter(function ($reminder) use ($search) {

                if (!$reminder->patient) {
                    return false;
                }

                $name = strtolower($reminder->patient->name ?? '');
                $surname = strtolower($reminder->patient->surname ?? '');

                return str_contains($name, $search)
                    || str_contains($surname, $search);
            });
        }

        // Ricreiamo una paginazione compatibile con Inertia
        $page = $request->get('page', 1);
        $perPage = 20;

        $reminders = new \Illuminate\Pagination\LengthAwarePaginator(
            $reminders->forPage($page, $perPage),
            $reminders->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return inertia('Reminders/IndexReminders', [
            'reminders' => $reminders,
            'reminderTypes' => ReminderType::all(),
            'filters' => $request->only([
                'status',
                'reminder_type_id',
                'date_from',
                'date_to',
                'patient',
            ]),
        ]);
    }

    public function show(AppointmentReminder $reminder)
    {
        $reminder->load([
            'appointment',
            'patient',
            'reminderType'
        ]);

        return Inertia::render('Reminders/ShowReminder', ['reminder' => $reminder]);
    }
}
