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
        .totals td { border: none; }
        .totals tr:last-child td { font-weight: bold; border-top: 1px solid #1f2937; }
    </style>
</head>
<body>
    <h1>Invoice {{ $order->order_number }}</h1>
    <p class="muted">Tanggal: {{ $order->created_at->format('d F Y H:i') }}</p>

    <p>
        <strong>Penerima:</strong> {{ $order->recipient_name }}<br>
        {{ $order->phone }}<br>
        {{ $order->address_line }}, {{ $order->city }} {{ $order->postal_code }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Varian</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->variant_label }}</td>
                    <td class="text-right">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>Ongkos Kirim ({{ $order->shipping_zone_name }})</td><td class="text-right">Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</td></tr>
        @if ($order->discount > 0)
            <tr><td>Diskon</td><td class="text-right">-Rp{{ number_format($order->discount, 0, ',', '.') }}</td></tr>
        @endif
        <tr><td>Total</td><td class="text-right">Rp{{ number_format($order->total, 0, ',', '.') }}</td></tr>
    </table>
</body>
</html>
