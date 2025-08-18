<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de salida #{{ $output->id }}</title>
    <style>
        @media print {
            @page { size: 80mm auto; margin: 5mm; }
            body { width: 72mm; }
        }
        body { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; }
        .center { text-align: center; }
        .muted { color: #555; }
        .row { display: flex; justify-content: space-between; }
        .divider { border-top: 1px dashed #333; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 2px 0; }
    </style>
</head>
<body onload="window.print()">
    <div class="center" style="margin-bottom:6px;">
        <img src="/images/armada-logo.png" alt="Armada" style="height:56px; display:block; margin:0 auto 4px;">
        <strong>{{ config('app.name', 'Farmacia') }}</strong><br>
        <span class="muted">Ticket de salida #{{ $output->id }}</span>
    </div>

    <div class="divider"></div>

    <div>
        <div class="row"><span>Fecha:</span><span>{{ $output->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Depto. destino:</span><span>{{ $output->department?->name }}</span></div>
        <div class="row"><span>Paciente:</span><span>{{ $output->patient_type === 'military' ? 'Militar' : ($output->patient_type === 'department' ? 'Departamento' : 'Civil') }}</span></div>
        @if($output->patient_name)
            <div class="row"><span>Nombre paciente:</span><span>{{ $output->patient_name }}</span></div>
        @endif
        @if($output->doctor_name)
            <div class="row"><span>Médico:</span><span>{{ $output->doctor_name }}</span></div>
        @endif
        <div class="row"><span>Despachado por:</span><span>{{ $output->user?->name }}</span></div>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th style="text-align:right">Cant.</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($output->items as $item)
                @php $total += (int) $item->quantity; @endphp
                <tr>
                    <td>{{ $item->medication?->name }}</td>
                    <td style="text-align:right">{{ (int) $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>
    <div class="row"><strong>Total unidades</strong><strong>{{ $total }}</strong></div>

    @if($output->reason)
        <div class="divider"></div>
        <div>
            <strong>Motivo:</strong>
            <div>{{ $output->reason }}</div>
        </div>
    @endif

    <div class="center muted" style="margin-top:8px;">Gracias</div>
</body>
</html>
