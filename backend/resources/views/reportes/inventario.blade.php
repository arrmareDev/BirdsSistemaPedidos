<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 2px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 10px;
            margin-bottom: 14px;
        }

        .resumen {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }

        .resumen-item {
            display: table-cell;
            width: 25%;
            padding: 8px 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .resumen-item .label {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: .5px;
        }

        .resumen-item .valor {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #111827;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .num {
            text-align: right;
        }

        .estado-normal {
            color: #16a34a;
            font-weight: bold;
        }

        .estado-bajo {
            color: #d97706;
            font-weight: bold;
        }

        .estado-agotado {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 16px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Reporte de Inventario — Florería Birds</h1>
    <p class="subtitle">Generado el {{ $generado }} · {{ $productos->count() }} productos con control de stock</p>

    <div class="resumen">
        <div class="resumen-item">
            <div class="label">Total productos</div>
            <div class="valor">{{ $productos->count() }}</div>
        </div>
        <div class="resumen-item">
            <div class="label">Stock bajo</div>
            <div class="valor">{{ $productos->where('stock_bajo', true)->count() }}</div>
        </div>
        <div class="resumen-item">
            <div class="label">Agotados</div>
            <div class="valor">{{ $productos->where('stock', '<=', 0)->count() }}</div>
        </div>
        <div class="resumen-item">
            <div class="label">Valor en inventario</div>
            <div class="valor">S/ {{ number_format($valorTotal, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="num">Stock</th>
                <th class="num">Mínimo</th>
                <th>Estado</th>
                <th class="num">Precio</th>
                <th class="num">Valor en inventario</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
                @php
                    $estado = $p->stock <= 0 ? 'agotado' : ($p->stock_bajo ? 'bajo' : 'normal');
                    $estadoLabel = $p->stock <= 0 ? 'Agotado' : ($p->stock_bajo ? 'Stock bajo' : 'Normal');
                @endphp
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->category?->name ?? '—' }}</td>
                    <td class="num">{{ $p->stock }}</td>
                    <td class="num">{{ $p->stock_minimo ?? '—' }}</td>
                    <td class="estado-{{ $estado }}">{{ $estadoLabel }}</td>
                    <td class="num">S/ {{ number_format((float) $p->price, 2) }}</td>
                    <td class="num">S/ {{ number_format($p->stock * (float) $p->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Valor total en inventario: S/ {{ number_format($valorTotal, 2) }}</p>
</body>

</html>
