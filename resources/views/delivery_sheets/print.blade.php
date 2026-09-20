<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Sheet - {{ $sheet->sheet_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; padding: 20px; }
        .sheet-box { max-width: 800px; margin: auto; border: 1px solid #000; padding: 15px; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        @media print { .no-print { display: none; } .sheet-box { border: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer;">Print Delivery Sheet</button>
    </div>

    <div class="sheet-box">
        <div class="header">
            <h2>DELIVERY DISPATCH SHEET</h2>
            <p><strong>Sheet No:</strong> {{ $sheet->sheet_number }} | <strong>Date:</strong> {{ $sheet->sheet_date->format('d/m/Y') }}</p>
            <p><strong>Driver:</strong> {{ $sheet->driver_name ?? 'N/A' }} | <strong>Vehicle No:</strong> {{ $sheet->vehicle_number ?? 'N/A' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Amount (₹)</th>
                    <th>Receiver Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sheet->items as $idx => $it)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $it->invoice->invoice_number }}</strong></td>
                    <td>{{ $it->invoice->invoice_date->format('d/m/Y') }}</td>
                    <td>{{ $it->invoice->customer_name }}</td>
                    <td>{{ $it->invoice->customer_address }}</td>
                    <td>₹{{ number_format($it->invoice->grand_total, 2) }}</td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
