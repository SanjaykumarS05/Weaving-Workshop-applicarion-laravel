@extends('layouts.app')

@section('title', 'Delivery Sheets - GST Billing Application')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;" data-i18n="nav.deliverySheet">Delivery Sheets</h2>
        <button onclick="showCreateDeliverySheetModal()" class="btn primary">+ Create Delivery Sheet</button>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Sheet No</th>
                    <th>Date</th>
                    <th>Driver Name</th>
                    <th>Vehicle No</th>
                    <th>Invoices Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sheets as $st)
                <tr>
                    <td><strong>{{ $st->sheet_number }}</strong></td>
                    <td>{{ $st->sheet_date->format('d/m/Y') }}</td>
                    <td>{{ $st->driver_name ?? '-' }}</td>
                    <td>{{ $st->vehicle_number ?? '-' }}</td>
                    <td><span class="badge secondary">{{ $st->items->count() }} Invoices</span></td>
                    <td style="display: flex; gap: 8px;">
                        <a href="{{ route('delivery-sheets.print', $st->id) }}" target="_blank" class="btn secondary" style="padding: 4px 10px; font-size: 0.8rem;">Print Sheet</a>
                        <button onclick="deleteSheet({{ $st->id }})" class="btn danger" type="button" style="padding: 4px 10px; font-size: 0.8rem;">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 24px;">No delivery sheets found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Delivery Sheet Modal -->
<div id="createSheetModal" class="modal-backdrop hidden">
    <div class="modal-card" style="width: min(600px, 100%);">
        <h3 style="margin-top: 0;">Create Delivery Sheet</h3>
        <form id="createSheetForm" class="stack">
            <div class="grid two">
                <label>
                    <span>Sheet Date</span>
                    <input id="sheetDate" type="date" required value="{{ date('Y-m-d') }}">
                </label>
                <label>
                    <span>Driver Name</span>
                    <input id="driverName" type="text" placeholder="Driver Name">
                </label>
            </div>
            <div class="grid two">
                <label>
                    <span>Vehicle Number</span>
                    <input id="vehicleNumber" type="text" placeholder="TN 01 AB 1234">
                </label>
                <label>
                    <span>Notes</span>
                    <input id="sheetNotes" type="text" placeholder="Remarks...">
                </label>
            </div>

            <div>
                <label style="margin-bottom: 8px;">Select Invoices to Include:</label>
                <div id="invoiceCheckboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                    <span style="color: var(--muted);">Loading pending invoices...</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button onclick="hideModal('createSheetModal')" class="btn secondary" type="button">Cancel</button>
                <button class="btn primary" type="submit">Create & Print</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function showCreateDeliverySheetModal() {
    showModal('createSheetModal');
    try {
        const data = await apiFetch('/invoices');
        const container = document.getElementById('invoiceCheckboxes');
        container.innerHTML = '';

        if (!data.invoices.data || data.invoices.data.length === 0) {
            container.innerHTML = '<span style="color: var(--muted);">No invoices available.</span>';
            return;
        }

        data.invoices.data.forEach(inv => {
            const label = document.createElement('label');
            label.style.flexDirection = 'row';
            label.style.alignItems = 'center';
            label.style.gap = '8px';
            label.style.fontWeight = 'normal';
            label.innerHTML = `
                <input type="checkbox" name="selected_invoices" value="${inv.id}">
                <span><strong>${inv.invoice_number}</strong> - ${inv.customer_name} (₹${inv.grand_total})</span>
            `;
            container.appendChild(label);
        });
    } catch (e) {
        console.error(e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('createSheetForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const selected = Array.from(document.querySelectorAll('input[name="selected_invoices"]:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one invoice.');
            return;
        }

        const payload = {
            sheet_date: document.getElementById('sheetDate').value,
            driver_name: document.getElementById('driverName').value,
            vehicle_number: document.getElementById('vehicleNumber').value,
            notes: document.getElementById('sheetNotes').value,
            invoice_ids: selected,
        };

        try {
            const res = await apiFetch('/api/delivery-sheets', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            if (res.success) {
                window.open(res.print_url, '_blank');
                window.location.reload();
            }
        } catch (err) {
            alert('Failed to create delivery sheet: ' + err.message);
        }
    });
});

async function deleteSheet(id) {
    if (!confirm('Are you sure you want to delete this delivery sheet?')) return;
    try {
        const res = await apiFetch(`/api/delivery-sheets/${id}`, { method: 'DELETE' });
        if (res.success) window.location.reload();
    } catch (e) {
        alert('Failed to delete sheet: ' + e.message);
    }
}
</script>
@endpush
