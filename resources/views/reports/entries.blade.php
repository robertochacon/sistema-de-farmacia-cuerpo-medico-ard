<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <div style="text-align:center; margin-bottom:8px;">
        <img src="{{ public_path('images/armada-logo.png') }}" alt="Armada" style="height:120px;">
        <div style="font-weight:bold; margin-top:4px;">Farmacia de la Armada</div>
    </div>
    <h2>Reporte de entradas</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Empresa/Institución</th>
                <th>Detalle</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($entries as $e)
            @php $total = (int) $e->items->sum('quantity'); @endphp
            <tr>
                <td>{{ $e->id }}</td>
                <td>{{ optional($e->received_at)->format('d/m/Y') ?? optional($e->created_at)->format('d/m/Y') }}</td>
                <td>
                    {{ $e->entry_type === 'donation' ? 'Donación' : ($e->entry_type === 'order' ? 'Pedido' : 'Compra') }}
                </td>
                <td>{{ $e->organization?->name }}</td>
                <td>
                    @foreach($e->items as $item)
                        <div>{{ $item->medication?->name }} ({{ (int) $item->quantity }})</div>
                    @endforeach
                </td>
                <td>{{ $total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
