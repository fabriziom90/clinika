<!doctype html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }
    </style>
</head>

<body>
    <h2>Fattura {{ $invoice->number }}</h2>
    <p>
        Data:
        {{ $invoice->date }}
    </p>
    <p>
        Paziente:
        {{ $invoice->full_name }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Prestazione</th>
                <th>Qtà</th>
                <th>Prezzo</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($invoice->invoiceItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h3>
        Totale
        {{ number_format($invoice->amount, 2, ',', '.') }} €
    </h3>

</body>

</html>
