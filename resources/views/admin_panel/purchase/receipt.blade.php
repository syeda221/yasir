<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Receipt - {{ $purchase->invoice_no }}</title>
    <style>
        @media print {
            body {
                width: 72mm;
                margin: 0;
                padding: 0;
                font-family: 'Courier New', Courier, monospace;
            }

            .no-print {
                display: none;
            }

            @page {
                size: 72mm auto;
                margin: 0;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            width: 72mm;
            /* Preview width */
            margin: 0 auto;
            padding: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .sub-header {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .items-table th,
        .items-table td {
            text-align: left;
            vertical-align: top;
            padding: 2px 0;
        }

        .items-table th {
            border-bottom: 1px solid #000;
            font-size: 10px;
        }

        .text-end {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .totals-section {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }

        .btn-print {
            padding: 10px;
            text-align: center;
            background: #eee;
            margin-bottom: 10px;
            cursor: pointer;
            display: block;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>

    <a href="javascript:window.print()" class="btn-print no-print">PRINT RECEIPT</a>

    <div class="header">
        <div class="company-name">{{ \App\Models\Setting::get('company_name', 'prowave technogies') }}</div>
        <div class="sub-header">{{ \App\Models\Setting::get('company_address', 'Hyderabad') }}</div>
        <div class="sub-header">{{ \App\Models\Setting::get('company_phone', '0327-9226901') }}</div>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span>Inv #: {{ $purchase->invoice_no }}</span>
        <span>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</span>
    </div>
    <div class="info-row">
        <span>Vendor: {{ Str::limit($purchase->vendor->name ?? 'N/A', 15) }}</span>
    </div>
    @if (auth()->check())
        <div class="info-row">
            <span>User: {{ auth()->user()->name }}</span>
        </div>
    @endif

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Item</th>
                <th style="width: 20%;" class="text-center">Qty</th>
                <th style="width: 35%;" class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                @php
                    // Size mode logic for display
                    $piecesPerBox = (int) ($item->pieces_per_box ?? 1);
                    if ($piecesPerBox <= 0 && $item->product && $item->product->pieces_per_box > 0) {
                        $piecesPerBox = (int)$item->product->pieces_per_box;
                    }
                    $sizeMode = $item->size_mode ?? ($item->product->size_mode ?? 'by_pieces');
                    $itemUnit = strtolower(trim($item->unit ?? ''));
                    $isPcsUnit = in_array($itemUnit, ['pcs', 'pc', 'piece', 'pieces']);
                    $isCartonUnit = in_array($itemUnit, ['box', 'carton', 'ctn']);
                    $isCartonMode = !$isPcsUnit && ($isCartonUnit || $sizeMode === 'by_cartons');

                    $rawQtyStr = (string) $item->qty;
                    $rawQty = (float) $item->qty;

                    if ($isPcsUnit) {
                        $qtyDisplay = "$rawQty Pcs";
                    } elseif ($isCartonMode && $piecesPerBox > 1) {
                        if (strpos($rawQtyStr, '.') !== false) {
                            $parts = explode('.', $rawQtyStr);
                            $boxes = (int) ($parts[0] ?? 0);
                            $loose = (int) ($parts[1] ?? 0);
                            $qtyDisplay = "$boxes.$loose Box";
                        } else {
                            $qtyDisplay = "$rawQty Box";
                        }
                    } else {
                        $qtyDisplay = $rawQty . ' ' . ($item->unit ?? 'Pcs');
                    }
                @endphp
                <tr>
                    <td colspan="3" style="font-weight: 600;">
                        {{ \Illuminate\Support\Str::limit($item->product->item_name ?? 'Item', 25) }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px; padding-left: 5px;">
                        {{ number_format($item->price, 2) }} x
                    </td>
                    <td class="text-center">{{ $qtyDisplay }}</td>
                    <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="info-row">
            <span>Subtotal:</span>
            <span>{{ number_format($purchase->subtotal, 2) }}</span>
        </div>

        @if ($purchase->additional_discount > 0)
            <div class="info-row">
                <span>Additional Discount:</span>
                <span>-{{ number_format($purchase->additional_discount, 2) }}</span>
            </div>
        @endif

        @if ($purchase->extra_cost > 0)
            <div class="info-row">
                <span>Extra Cost:</span>
                <span>{{ number_format($purchase->extra_cost, 2) }}</span>
            </div>
        @endif

        <div class="total-row" style="font-size: 14px; border-top: 1px dashed #000; margin-top: 5px; padding-top: 2px;">
            <span>Total Net:</span>
            <span>{{ number_format($purchase->net_amount, 2) }}</span>
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span>Paid:</span>
            <span>{{ number_format($purchase->paid_amount, 2) }}</span>
        </div>

        <div class="info-row" style="font-weight: bold;">
            <span>Bill Due:</span>
            <span>{{ number_format($purchase->net_amount - $purchase->paid_amount, 2) }}</span>
        </div>

        <div class="info-row">
            <span>Previous Bal:</span>
            <span>{{ number_format($previousBalance, 2) }}</span>
        </div>

        <div class="info-row" style="font-weight: bold; font-size: 14px; border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px;">
            <span>Total Closing Bal:</span>
            <span>{{ number_format($currentBalance, 2) }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="footer">
        <p>Purchase Record</p>
        <p style="font-size: 9px;">Software by: Antigravity AI</p>
    </div>

</body>

</html>
