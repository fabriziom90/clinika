<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Conferma prenotazione</title>
</head>

<body>

    <h1>Prenotazione effettuata</h1>

    <p>
        Gentile {{ $appointment->patient->name }}
        {{ $appointment->patient->surname }},
    </p>

    <p>
        la tua prenotazione è stata registrata correttamente.
    </p>

    <p>
        Grazie per aver utilizzato Clinika.
    </p>

</body>

</html>
