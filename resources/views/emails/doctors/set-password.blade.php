<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imposta la tua password - Clinika</title>
    <style>
        /* Montserrat Regular */
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.gstatic.com/s/montserrat/v25/JTUHjIg1_i6t8kCHKm45xW4.ttf') format('truetype');
        }

        /* Montserrat Bold */
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 700;
            src: url('https://fonts.gstatic.com/s/montserrat/v25/JTURjIg1_i6t8kCHKm45_bZF3gnD-w.ttf') format('truetype');
        }

        /* Roboto Regular */
        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu4mxK.woff2') format('woff2');
        }

        /* Roboto Bold */
        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 700;
            src: url('https://fonts.gstatic.com/s/roboto/v30/KFOlCnqEu92Fr1MmWUlfBBc9.ttf') format('truetype');
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f6f8fb;
            font-family: 'Roboto', Helvetica, Arial, sans-serif;
            color: #333;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        button,
        a {
            font-family: 'Montserrat', Helvetica, Arial, sans-serif;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .header {
            background-color: #c53238;
            color: #fff;
            text-align: center;
            padding: 30px 20px;
        }

        .header img {
            width: 120px;
            height: auto;
        }

        .content {
            padding: 30px 40px !important;
        }

        h1 {
            font-size: 22px;
            color: #c53238;
            margin-top: 0;
        }

        p {
            line-height: 1.6;
            margin: 12px 0;
        }

        .button {
            display: inline-block;
            background-color: #c53238;
            color: #ffffff !important;
            padding: 12px 24px;
            margin: 25px 0;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }

        .button:hover {
            background-color: #0E8984;
        }

        .footer {
            font-size: 13px;
            color: #888;
            text-align: center;
            padding: 20px;
            border-top: 1px solid #eee;
        }

        @media only screen and (max-width: 600px) {
            .content {
                padding: 20px;
            }

            .button {
                display: block;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            {{-- ✅ Usa il CID per il logo inline --}}
            <img src="{{ $logoCid ?? asset('images/logo_clinika.png') }}" alt="Clinika Logo">
        </div>

        <div class="content">
            <h1>Benvenuto in Clinika, {{ $user->name }} 👋</h1>

            <p>Il tuo account è stato creato con successo.</p>

            <p>Per motivi di sicurezza, imposta la tua password cliccando il pulsante qui sotto:</p>

            <p style="text-align: center;">
                <a href="{{ $url }}" class="button">Imposta Password</a>
            </p>

            <p>⚠️ Questo link scadrà dopo <strong>{{ config('auth.passwords.users.expire') }}</strong> minuti.</p>

            <p>Grazie per aver scelto <strong>{{ config('app.name') }}</strong>!</p>
        </div>

        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }}. Tutti i diritti riservati.
        </div>
    </div>
</body>

</html>
