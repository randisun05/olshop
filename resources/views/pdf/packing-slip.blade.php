<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #6b7280; }
        .notice { margin-top: 4px; font-style: italic; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f3f4f6; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Surat Jalan — {{ $order->order_number }}</h1>
    <p class="muted">Tanggal: {{ $order->created_at->format('d F Y H:i') }}</p>
    <p class="notice muted">Dokumen internal untuk pengemasan &amp; pengiriman — bukan invoice/bukti pembayaran.</p>

    <p>
        <strong>Kirim kepada:</strong><br>
        {{ $order->recipient_name }}<br>
        {{ $order->phone }}<br>
        {{ $order->address_line }}, {{ $order->city }} {{ $order->postal_code }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Varian</th>
                <th class="text-right">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->variant_label }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
