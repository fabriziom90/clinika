<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">

    <title>
        Referto Medico #{{ $version->id }}
    </title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #C53238;
            color: white;
            padding: 15px;
        }

        .logo {
            width: 80px;
            height: 80px;
        }

        .clinic-info {
            text-align: right;
        }


        .section {
            padding: 15px;
        }


        h2,
        h3 {
            margin: 5px 0;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }


        th,
        td {
            border: 1px solid #999;
            padding: 5px;
            text-align: left;
        }


        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 30px;
            color: #555;
        }


        .signature {
            margin-top: 50px;
        }

        .voided-box {
            margin-bottom: 25px;
            background-color: #E9363F;
            border: 1px solid #E9363F;
            border-radius: 10px;
            padding: 10px 0 10px 10px;
            color: #fff;
        }
    </style>

</head>


<body>


    <div class="header">

        <table style="border:0">

            <tr>

                <td style="border:0">
                    <img src="{{ public_path('images/logo_clinika.png') }}" class="logo">
                </td>


                <td style="border:0; text-align:right">

                    <strong>
                        Clinica Medica Fittizia
                    </strong>

                    <br>

                    Via della Salute 123
                    <br>

                    Roma

                    <br>

                    Tel: 06-1234567

                </td>

            </tr>

        </table>

    </div>



    <div class="section">
        @if ($version->is_voided)
            <div class="voided-box">
                <strong>DOCUMENTO ANNULLATO</strong><br>
                Questo referto è stato annullato.<br>

                @if ($version->void_reason)
                    Motivo: {{ $version->void_reason }}<br>
                @endif

                @if ($version->voided_at)
                    Data annullamento: {{ $version->voided_at }}
                @endif
            </div>
        @endif
        <h2>
            Referto Medico
        </h2>


        <p>
            <strong>Paziente:</strong>

            {{ $entry->appointment->patient->name }}

            {{ $entry->appointment->patient->surname }}

        </p>


        <p>
            <strong>Data appuntamento:</strong>

            {{ $entry->appointment->start_time }}

        </p>


        <p>

            <strong>Medico:</strong>

            {{ $entry->doctor->user->name }}

            {{ $entry->doctor->user->surname }}

        </p>


    </div>



    <div class="section">


        <h3>

            {{ $version->title }}

            -

            {{ ucfirst($version->type) }}

        </h3>


        <p>

            {{ $version->content }}

        </p>


    </div>




    @if ($version->vitalParameters)
        <div class="section">


            <h3>
                Parametri Vitali
            </h3>


            <table>

                <tr>

                    <th>
                        Pressione
                    </th>

                    <th>
                        Frequenza Cardiaca
                    </th>

                    <th>
                        Temperatura
                    </th>

                    <th>
                        Peso
                    </th>

                    <th>
                        Altezza
                    </th>

                </tr>


                <tr>

                    <td>
                        {{ $version->vitalParameters->pressure ?? '-' }}
                    </td>


                    <td>
                        {{ $version->vitalParameters->heart_rate ?? '-' }}
                        bpm
                    </td>


                    <td>
                        {{ $version->vitalParameters->temperature ?? '-' }}
                        °C
                    </td>


                    <td>
                        {{ $version->vitalParameters->weight ?? '-' }}
                        kg
                    </td>


                    <td>
                        {{ $version->vitalParameters->height ?? '-' }}
                        cm
                    </td>


                </tr>


            </table>


        </div>
    @endif





    @if ($version->prescriptions->count())


        <div class="section">


            <h3>
                Prescrizioni
            </h3>



            <table>


                <tr>

                    <th>
                        Farmaco
                    </th>

                    <th>
                        Dosaggio
                    </th>

                    <th>
                        Frequenza
                    </th>

                    <th>
                        Durata
                    </th>

                    <th>
                        Note
                    </th>


                </tr>



                @foreach ($version->prescriptions as $prescription)
                    <tr>


                        <td>
                            {{ $prescription->drug_name }}
                        </td>


                        <td>
                            {{ $prescription->dosage }}
                        </td>


                        <td>
                            {{ $prescription->frequency }}
                        </td>


                        <td>
                            {{ $prescription->duration }}
                        </td>


                        <td>
                            {{ $prescription->notes }}
                        </td>


                    </tr>
                @endforeach


            </table>


        </div>


    @endif





    @if ($version->attachments->count())


        <div class="section">


            <h3>
                Allegati
            </h3>


            <ul>

                @foreach ($version->attachments as $attachment)
                    <li>
                        {{ $attachment->name }}
                    </li>
                @endforeach


            </ul>


        </div>


    @endif





    <div class="section signature">


        <p>
            ______________________________
        </p>


        <p>
            Firma del medico
        </p>


    </div>




    <div class="footer">

        Documento generato da Clinica Medica Fittizia

        <br>

        Versione referto:
        {{ $version->id }}

    </div>



</body>

</html>
