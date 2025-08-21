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
        @php
            $logoPath = public_path('images/armada-logo.png');
            $logoSrc = file_exists($logoPath)
                ? ('data:image/png;base64,' . base64_encode(@file_get_contents($logoPath)))
                : null;
        @endphp
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Armada" style="height:120px;">
        @endif
        <div style="font-weight:bold; margin-top:4px;">Farmacia de la Armada</div>
    </div>
    <h2>Reporte de inventario (existencia)</h2>
    @isset($errorMessage)
        <div style="color:#b91c1c; background:#fee2e2; padding:8px; border:1px solid #fecaca; margin:8px 0;">
            <strong>Error al generar PDF:</strong>
            <div>{{ $errorMessage }}</div>
            @if(!empty($errorTrace))
                <pre style="white-space:pre-wrap; font-size:11px; color:#7f1d1d;">{{ $errorTrace }}</pre>
            @endif
            <div style="margin-top:6px; color:#374151;">Mostrando versión HTML como alternativa.</div>
        </div>
    @endisset

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
        @php($dataRows = isset($rows) ? $rows : (isset($medications) ? $medications->map(fn($m)=>[
            'id' => $m->id,
            'name' => $m->name,
            'quantity' => (int) ($m->quantity ?? 0),
            'expiration' => isset($m->display_expiration) ? $m->display_expiration : (optional($m->expiration_date) instanceof \Carbon\CarbonInterface ? optional($m->expiration_date)->format('d/m/Y') : ''),
        ]) : collect()) )
        @foreach($dataRows as $r)
            <tr>
                <td>{{ $r['id'] }}</td>
                <td>{{ $r['name'] }}</td>
                <td>{{ (int) $r['quantity'] }}</td>
                <td>{{ $r['expiration'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
