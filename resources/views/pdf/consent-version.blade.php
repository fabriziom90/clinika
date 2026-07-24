<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">

    <title>
        {{ $consentVersion->consentType->name }}
        - Versione {{ $consentVersion->version }}
    </title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin-bottom: 5px;
            font-size: 22px;
        }

        .header p {
            margin: 2px 0;
        }

        .consent-content {
            margin-top: 25px;
            text-align: justify;
        }

        .signature-section {
            margin-top: 80px;
            page-break-inside: avoid;
        }

        .signature-date {
            margin-bottom: 50px;
        }

        .signature-container {
            width: 100%;
        }

        .signature-box {
            width: 45%;
            display: inline-block;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 45px;
            border-bottom: 1px solid #000;
            width: 90%;
        }

        .signature-label {
            margin-top: 8px;
        }

        .meta {
            font-size: 11px;
            color: #666;
        }

        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>
            {{ $consentVersion->consentType->name }}
        </h1>

        <p>
            <strong>
                Versione {{ $consentVersion->version }}
            </strong>
        </p>

        @if ($consentVersion->created_at)
            <div class="meta">
                Data pubblicazione:
                {{ $consentVersion->created_at->format('d/m/Y') }}
            </div>
        @endif
    </div>

    <div class="consent-content">
        {!! nl2br(e($consentVersion->content)) !!}
    </div>

    <div class="signature-section">

        <div class="signature-date">
            <strong>Data:</strong>
            ______________________________________
        </div>

        <div class="signature-container">

            <div class="signature-box">
                <div class="signature-line"></div>

                <div class="signature-label">
                    Firma del paziente
                </div>
            </div>

        </div>

    </div>
    <div class="footer">
        Documento generato automaticamente da Clinika.
    </div>

</body>

</html>

