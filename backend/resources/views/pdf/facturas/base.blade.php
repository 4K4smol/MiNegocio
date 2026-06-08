<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $factura->numero_completo ?: 'Factura '.$factura->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
        }
        .page { padding: 32px; }
        .header {
            border-bottom: 3px solid #2563eb;
            margin-bottom: 24px;
            padding-bottom: 18px;
        }
        .brand { width: 42%; }
        .qr-block {
            text-align: center;
            width: 24%;
        }
        .invoice-meta { text-align: right; width: 32%; }
        .brand, .qr-block, .invoice-meta { display: inline-block; vertical-align: top; }
        .qr-title {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .qr-image {
            display: block;
            height: 34mm;
            margin: 0 auto 4px;
            width: 34mm;
        }
        .qr-legal {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.25;
            margin: 0;
        }
        h1 {
            font-size: 26px;
            letter-spacing: 0;
            margin: 0 0 8px;
            text-transform: uppercase;
        }
        h2 {
            color: #1f2937;
            font-size: 14px;
            margin: 0 0 8px;
            text-transform: uppercase;
        }
        p { margin: 0 0 4px; }
        .muted { color: #6b7280; }
        .box {
            border: 1px solid #e5e7eb;
            margin-bottom: 16px;
            padding: 12px;
        }
        .col {
            display: inline-block;
            vertical-align: top;
            width: 49%;
        }
        table {
            border-collapse: collapse;
            margin-top: 8px;
            width: 100%;
        }
        th {
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10px;
            padding: 8px;
            text-align: left;
            text-transform: uppercase;
        }
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }
        .right { text-align: right; }
        .totals {
            margin-left: auto;
            margin-top: 16px;
            width: 45%;
        }
        .totals td { padding: 6px 8px; }
        .totals .total td {
            border-top: 2px solid #111827;
            font-size: 15px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    @php
        $money = fn ($value) => number_format((float) $value, 2, ',', '.').' EUR';
        $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '-';
        $estado = $factura->estadoFactura?->codigo ?: '-';
    @endphp

    <div class="page">
        <div class="header">
            <div class="brand">
                <h1>Factura</h1>
                <p><strong>{{ $factura->emisor_nombre_razon_social }}</strong></p>
                <p>NIF/CIF: {{ $factura->emisor_nif }}</p>
                <p>{{ $factura->emisor_domicilio_fiscal }}</p>
                @if($factura->empresa?->correo)
                    <p>{{ $factura->empresa->correo }}</p>
                @endif
                @if($factura->empresa?->telefono)
                    <p>{{ $factura->empresa->telefono }}</p>
                @endif
            </div>
            <div class="qr-block">
                @if($qrImageDataUri)
                    <p class="qr-title">QR tributario:</p>
                    <img class="qr-image" src="{{ $qrImageDataUri }}" alt="QR tributario">
                    <p class="qr-legal">{{ $qrLegalText }}</p>
                @endif
            </div>
            <div class="invoice-meta">
                <p class="muted">Numero</p>
                <p><strong>{{ $factura->numero_completo ?: ($factura->serie.' / '.$factura->numero) }}</strong></p>
                <p class="muted">Fecha de emision</p>
                <p>{{ $date($factura->fecha_emision) }}</p>
                <p class="muted">Estado</p>
                <p>{{ ucfirst(str_replace('_', ' ', $estado)) }}</p>
            </div>
        </div>

        <div class="box">
            <div class="col">
                <h2>Cliente</h2>
                <p><strong>{{ $factura->receptor_nombre_razon_social }}</strong></p>
                <p>NIF/CIF: {{ $factura->receptor_nif }}</p>
                <p>{{ $factura->receptor_domicilio_fiscal }}</p>
                <p>
                    {{ collect([$factura->receptor_cp, $factura->receptor_municipio, $factura->receptor_provincia, $factura->receptor_pais])->filter()->join(', ') }}
                </p>
            </div>
            <div class="col">
                <h2>Operacion</h2>
                <p>Tipo: {{ $factura->tipoFactura?->codigo ?: 'factura' }}</p>
                <p>Fecha operacion: {{ $date($factura->fecha_operacion) }}</p>
                <p>Vencimiento: {{ $date($factura->fecha_vencimiento) }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="right">Cantidad</th>
                    <th class="right">Precio</th>
                    <th class="right">Dto.</th>
                    <th class="right">IVA</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->lineas as $linea)
                    <tr>
                        <td>
                            <strong>{{ $linea->descripcion ?: $linea->servicio_nombre_snapshot }}</strong>
                            @if($linea->servicio_nombre_snapshot && $linea->descripcion !== $linea->servicio_nombre_snapshot)
                                <br><span class="muted">{{ $linea->servicio_nombre_snapshot }}</span>
                            @endif
                        </td>
                        <td class="right">{{ number_format((float) $linea->cantidad, 2, ',', '.') }}</td>
                        <td class="right">{{ $money($linea->precio_unitario) }}</td>
                        <td class="right">{{ number_format((float) ($linea->descuento_porcentaje ?? 0), 2, ',', '.') }}%</td>
                        <td class="right">{{ number_format((float) ($linea->iva_porcentaje ?? 0), 2, ',', '.') }}%</td>
                        <td class="right">{{ $money($linea->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tbody>
                <tr>
                    <td>Base imponible</td>
                    <td class="right">{{ $money($factura->base_imponible ?: $factura->subtotal) }}</td>
                </tr>
                <tr>
                    <td>IVA</td>
                    <td class="right">{{ $money($factura->total_iva ?: $factura->cuota_iva) }}</td>
                </tr>
                @if((float) $factura->total_retencion > 0)
                    <tr>
                        <td>Retencion</td>
                        <td class="right">- {{ $money($factura->total_retencion) }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td>Total</td>
                    <td class="right">{{ $money($factura->total) }}</td>
                </tr>
            </tbody>
        </table>

        @if(!empty($datosPago))
            <div class="box" style="margin-top: 18px;">
                <h2>Datos de pago</h2>
                @if(!empty($datosPago['titular']))
                    <p><strong>Titular:</strong> {{ $datosPago['titular'] }}</p>
                @endif
                @if(!empty($datosPago['iban']))
                    <p><strong>IBAN:</strong> {{ $datosPago['iban'] }}</p>
                @endif
                @if(!empty($datosPago['bic']))
                    <p><strong>BIC/SWIFT:</strong> {{ $datosPago['bic'] }}</p>
                @endif
                @if(!empty($datosPago['concepto']))
                    <p><strong>Concepto:</strong> {{ $datosPago['concepto'] }}</p>
                @endif
                @if(!empty($datosPago['instrucciones']))
                    <p>{{ $datosPago['instrucciones'] }}</p>
                @endif
            </div>
        @endif

        @if($factura->impuestos->count())
            <div class="box" style="margin-top: 18px;">
                <h2>Desglose de impuestos</h2>
                @foreach($factura->impuestos as $impuesto)
                    <p>
                        {{ $impuesto->impuesto_nombre ?: 'IVA' }}:
                        base {{ $money($impuesto->base_imponible ?: $impuesto->base) }},
                        tipo {{ number_format((float) ($impuesto->tipo_porcentaje ?? $impuesto->porcentaje ?? 0), 2, ',', '.') }}%,
                        cuota {{ $money($impuesto->cuota) }}
                    </p>
                @endforeach
            </div>
        @endif

        @if($factura->observaciones)
            <div class="box">
                <h2>Observaciones</h2>
                <p>{{ $factura->observaciones }}</p>
            </div>
        @endif
    </div>
</body>
</html>
