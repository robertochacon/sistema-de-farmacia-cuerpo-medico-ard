<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de salidas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        @media print { @page { size: A4 portrait; margin: 10mm; } }
    </style>
</head>
<body onload="window.print()">
    <div style="text-align:center; margin-bottom:8px;">
        <img src="/images/armada-logo.png" alt="Armada" style="height:120px;">
        <div style="font-weight:bold; margin-top:4px;">Farmacia de la Armada</div>
    </div>
    <h2>Reporte de salidas</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Departamento</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Detalle</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($outputs as $o)
            @php $total = (int) $o->items->sum('quantity'); @endphp
            <tr>
                <td>{{ $o->id }}</td>
                <td>{{ $o->created_at->format('d/m/Y') }}</td>
                <td>{{ $o->department?->name }}</td>
                <td>
                    {{ $o->patient_type === 'military' ? 'Militar' : ($o->patient_type === 'department' ? 'Departamento' : 'Civil') }}
                    @if($o->patient_name)
                        <div style="color:#555;">{{ $o->patient_name }}</div>
                    @endif
                </td>
                <td>
                    @if($o->doctor_name)
                        {{ $o->doctor_name }}
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
    <script>
        (function () {
            function closeAfterPrint() { setTimeout(function () { try { window.close(); } catch (e) {} }, 300); }
            window.addEventListener('afterprint', closeAfterPrint);
            if (window.matchMedia) {
                var mql = window.matchMedia('print');
                if (mql && mql.addListener) mql.addListener(function (e) { if (!e.matches) closeAfterPrint(); });
            }
        })();
    </script>
</body>
</html>
