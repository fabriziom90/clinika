<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Referto Medico #{{ $entry->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Intestazione colorata */
        .header {
            background-color: #C53238;
            color: #fff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .logo {
            width: 80px;
            height: 80px;
            background-color: #ecf0f1;
            text-align: center;
            line-height: 80px;
            font-weight: bold;
            color: #2c3e50;
        }

        .header .clinic-info {
            text-align: right;
        }

        .section {
            padding: 15px;
        }

        h2,
        h3 {
            margin: 5px 0;
        }

        .vital-params,
        .prescriptions {
            margin-top: 10px;
            border-collapse: collapse;
            width: 100%;
        }

        .vital-params th,
        .vital-params td,
        .prescriptions th,
        .prescriptions td {
            border: 1px solid #999;
            padding: 5px;
        }

        .attachments ul {
            padding-left: 20px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 30px;
            color: #555;
        }
    </style>
</head>

<body>

    <!-- INTESTAZIONE -->
    <div class="header">
        <div class="logo"><img src="{{ public_path('images/logo_clinika.png') }}" class="logo" alt="Logo struttura">
        </div>
        <div class="clinic-info">
            <strong>Clinica Medica Fittizia</strong><br>
            Via della Salute 123, 00100 Roma<br>
            Tel: 06-1234567
        </div>
    </div>

    <!-- INFORMAZIONI PAZIENTE -->
    <div class="section">
        <h2>Referto Medico</h2>
        <p><strong>Paziente:</strong> {{ $entry->appointment->patient->name ?? 'Mario' }}
            {{ $entry->appointment->patient->surname ?? 'Rossi' }}</p>
        <p><strong>Data Appuntamento:</strong> {{ $entry->appointment->start_time ?? '01/01/2026 09:00' }}</p>
        <p><strong>Medico:</strong> {{ $entry->doctor->user->name ?? 'Giovanni' }}
            {{ $entry->doctor->user->surname ?? 'Verdi' }}</p>
    </div>

    <!-- CONTENUTO REFERTI -->
    <div class="section">
        <h3>{{ $version->title ?? 'Visita Medica' }} ({{ ucfirst($version->type ?? 'visit') }})</h3>
        <p>{{ $version->content ?? 'Nessun contenuto disponibile' }}</p>
    </div>

    <!-- PARAMETRI VITALI -->
    @if ($version->vitalParameters)
        <div class="section">
            <h3>Parametri Vitali</h3>
            <table class="vital-params">
                <tr>
                    <th>Pressione</th>
                    <th>Frequenza Cardiaca</th>
                    <th>Temperatura</th>
                    <th>Peso</th>
                    <th>Altezza</th>
                </tr>
                <tr>
                    <td>{{ $version->vitalParameters->pressure ?? '-' }}</td>
                    <td>{{ $version->vitalParameters->heart_rate ?? '-' }} bpm</td>
                    <td>{{ $version->vitalParameters->temperature ?? '-' }} °C</td>
                    <td>{{ $version->vitalParameters->weight ?? '-' }} kg</td>
                    <td>{{ $version->vitalParameters->height ?? '-' }} cm</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- PRESCRIZIONI -->
    @if ($version->prescriptions && $version->prescriptions->count() > 0)
        <div class="section">
            <h3>Prescrizioni</h3>
            <table class="prescriptions">
                <tr>
                    <th>Farmaco</th>
                    <th>Dosaggio</th>
                    <th>Frequenza</th>
                    <th>Durata</th>
                    <th>Note</th>
                </tr>
                @foreach ($version->prescriptions as $prescription)
                    <tr>
                        <td>{{ $prescription->drug_name }}</td>
                        <td>{{ $prescription->dosage }}</td>
                        <td>{{ $prescription->frequency }}</td>
                        <td>{{ $prescription->duration }}</td>
                        <td>{{ $prescription->notes }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <!-- ALLEGATI -->
    @if ($version->attachments && $version->attachments->count() > 0)
        <div class="section attachments">
            <h3>Allegati</h3>
            <ul>
                @foreach ($version->attachments as $attachment)
                    <li><a href="{{ $attachment->url }}">{{ $attachment->name }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- FIRMA MEDICO -->
    <div class="section" style="margin-top:40px;">
        <p>__________________________</p>
        <p>Firma del Medico</p>
    </div>

    <div class="footer">
        Documento generato da Clinica Medica Fittizia
    </div>

</body>

</html>
