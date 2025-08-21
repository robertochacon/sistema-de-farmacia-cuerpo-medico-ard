<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de inventario (existencia)</title>
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
    <h2>Reporte de inventario (existencia)</h2>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Existencia</th>
                <th>Vence</th>
            </tr>
        </thead>
        <tbody>
        @foreach($medications as $m)
            <tr>
                <td>{{ $m->id }}</td>
                <td>{{ $m->name }}</td>
                <td>{{ (int) ($m->quantity ?? 0) }}</td>
                <td>{{ optional($m->expiration_date)->format('d/m/Y') }}</td>
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
