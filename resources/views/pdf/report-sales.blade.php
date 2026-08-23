<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f3f4f6; }
        .text-right { text-align: right; }
        .summary td { border: none; padding: 2px 8px; }
        .summary tr:last-child td { font-weight: bold; border-top: 1px solid #1f2937; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <p class="muted">Periode: {{ $from }} s/d {{ $to }}</p>

    <table class="summary">
        <tr><td>Jumlah Pesanan</td><td class="text-right">{{ $orders->count() }}</td></tr>
        <tr><td>Total Omzet</td><td class="text-right">Rp{{ number_format($orders->sum('total'), 0, ',', '.') }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Nomor Pesanan</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->user->name ?? $order->guest_name }}</td>
                    <td>{{ $order->status->label() }}</td>
                    <td class="text-right">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
