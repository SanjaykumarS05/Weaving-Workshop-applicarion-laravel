<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .invoice-box {
            max-width: 850px;
            margin: auto;
            border: 1px solid #000;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
        }
        .grid {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .grid-col {
            width: 48%;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.items th, table.items td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        table.items th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .bank-details {
            width: 60%;
            border: 1px solid #000;
            padding: 8px;
        }
        .signature {
            width: 35%;
            text-align: center;
            border: 1px solid #000;
            padding: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .invoice-box { border: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Print Invoice</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <h1>TAX INVOICE</h1>
            <h2>{{ $setting->profile_name ?? Auth::user()->business_name }}</h2>
            <p>{{ $setting->profile_address }}</p>
            <p><strong>GSTIN:</strong> {{ $setting->profile_gstin ?? 'N/A' }} | <strong>Phone:</strong> {{ $setting->profile_phone }} | <strong>Email:</strong> {{ $setting->profile_email }}</p>
        </div>

        <div class="grid">
            <div class="grid-col">
                <strong>Billed To:</strong><br>
                <strong>{{ $invoice->customer_name }}</strong><br>
                {{ $invoice->customer_address }}<br>
                @if($invoice->customer_gstin) <strong>GSTIN:</strong> {{ $invoice->customer_gstin }}<br> @endif
                @if($invoice->customer_phone) <strong>Phone:</strong> {{ $invoice->customer_phone }}<br> @endif
                @if($invoice->customer_state) <strong>State:</strong> {{ $invoice->customer_state }} (Code: {{ $invoice->customer_state_code }})<br> @endif
            </div>
            <div class="grid-col">
                <strong>Invoice Details:</strong><br>
                <strong>Invoice No:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}<br>
                <strong>Supply Type:</strong> {{ strtoupper($invoice->supply_type) }}<br>
                @if($invoice->e_way_bill_no) <strong>e-Way Bill No:</strong> {{ $invoice->e_way_bill_no }}<br> @endif
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>HSN/SAC</th>
                    <th>Qty</th>
                    <th>Rate (₹)</th>
                    <th>Taxable (₹)</th>
                    @if($invoice->supply_type === 'intra')
                        <th>CGST</th>
                        <th>SGST</th>
                    @elseif($invoice->supply_type === 'inter')
                        <th>IGST</th>
                    @endif
                    <th class="text-right">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->hsn_code ?? '-' }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                    <td>{{ number_format($item->rate, 2) }}</td>
                    <td>{{ number_format($item->taxable_amount, 2) }}</td>
                    @if($invoice->supply_type === 'intra')
                        <td>{{ number_format($item->cgst_amount, 2) }} ({{ $item->tax_rate / 2 }}%)</td>
                        <td>{{ number_format($item->sgst_amount, 2) }} ({{ $item->tax_rate / 2 }}%)</td>
                    @elseif($invoice->supply_type === 'inter')
                        <td>{{ number_format($item->igst_amount, 2) }} ({{ $item->tax_rate }}%)</td>
                    @endif
                    <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                    <td colspan="{{ $invoice->supply_type === 'intra' ? 3 : ($invoice->supply_type === 'inter' ? 2 : 1) }}">₹{{ number_format($invoice->subtotal, 2) }}</td>
                    <td class="text-right">₹{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td colspan="5" class="text-right">Discount:</td>
                    <td colspan="{{ $invoice->supply_type === 'intra' ? 3 : ($invoice->supply_type === 'inter' ? 2 : 1) }}"></td>
                    <td class="text-right">- ₹{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->round_off != 0)
                <tr>
                    <td colspan="5" class="text-right">Round Off:</td>
                    <td colspan="{{ $invoice->supply_type === 'intra' ? 3 : ($invoice->supply_type === 'inter' ? 2 : 1) }}"></td>
                    <td class="text-right">₹{{ number_format($invoice->round_off, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                    <td colspan="{{ $invoice->supply_type === 'intra' ? 3 : ($invoice->supply_type === 'inter' ? 2 : 1) }}"></td>
                    <td class="text-right"><strong>₹{{ number_format($invoice->grand_total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <div class="bank-details">
                <strong>Bank Account Details:</strong><br>
                Bank Name: {{ $setting->profile_bank_name ?? 'N/A' }}<br>
                A/C No: {{ $setting->profile_account_no ?? 'N/A' }}<br>
                Branch: {{ $setting->profile_branch_name ?? 'N/A' }} | IFSC: {{ $setting->profile_ifsc ?? 'N/A' }}<br><br>
                <small><em>Declaration: {{ $setting->profile_declaration }}</em></small>
            </div>
            <div class="signature">
                <div><strong>For {{ $setting->profile_name ?? Auth::user()->business_name }}</strong></div>
                <div style="height: 40px;"></div>
                <div>{{ $setting->profile_signatory ?? 'Authorised Signatory' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
