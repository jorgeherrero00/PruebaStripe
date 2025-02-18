<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Facturas</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .download-button {
            display: inline-block;
            padding: 8px 12px;
            margin: 5px 0;
            color: white;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>Mis Facturas</h2>
    
    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <table>
        <tr>
            <th>Fecha</th>
            <th>Total</th>
            <th>Descargar</th>
        </tr>
        @forelse($invoices as $invoice)
        <tr>
            <td>{{ $invoice->date()->toFormattedDateString() }}</td>
            <td>{{ $invoice->total() }}</td>
            <td>
                <a href="{{ url('/invoice/' . $invoice->id) }}" class="download-button">Descargar PDF</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3">No tienes facturas disponibles.</td>
        </tr>
        @endforelse
    </table>

</body>
</html>
