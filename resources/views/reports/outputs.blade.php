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
    <h2>Reporte de salidas</h2>
    <div style="margin-bottom:8px;">
        @php
            $from = data_get($filters, 'from');
            $until = data_get($filters, 'until');
        @endphp
        <div>Rango: {{ $from ?: 'N/A' }} - {{ $until ?: 'N/A' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Departamento</th>
                <th>Paciente</th>
                <th>Detalle</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($outputs as $o)
            @php $total = (int) $o->items->sum('quantity'); @endphp
            <tr>
                <td>{{ $o->id }}</td>
                <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $o->department?->name }}</td>
                <td>
                    {{ $o->patient_type === 'military' ? 'Militar' : ($o->patient_type === 'department' ? 'Departamento' : 'Civil') }}
                    @if($o->patient_name)
                        <div style="color:#555;">{{ $o->patient_name }}</div>
                    @endif
                </td>
                <td>
                    @foreach($o->items as $item)
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
