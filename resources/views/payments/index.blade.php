@extends('layouts.app')

@section('title', 'Payments - GST Billing Application')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;" data-i18n="nav.payments">Payments History</h2>
        <button onclick="showRecordPaymentModal()" class="btn primary">+ Record Payment</button>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Payment No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Invoice No</th>
                    <th>Payment Mode</th>
                    <th>Amount (₹)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                <tr>
                    <td><strong>{{ $pay->payment_number }}</strong></td>
                    <td>{{ $pay->payment_date->format('d/m/Y') }}</td>
                    <td>{{ $pay->customer->name ?? '-' }}</td>
                    <td>{{ $pay->invoice->invoice_number ?? '-' }}</td>
                    <td><span class="badge secondary">{{ strtoupper($pay->payment_mode) }}</span></td>
                    <td><strong>₹{{ number_format($pay->amount, 2) }}</strong></td>
                    <td>
                        <button onclick="deletePayment({{ $pay->id }})" class="btn danger" style="padding: 4px 10px; font-size: 0.8rem;">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No payments recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Record Payment Modal -->
<div id="paymentModal" class="modal-backdrop hidden">
    <div class="modal-card">
        <h3 style="margin-top: 0;">Record Payment</h3>
        <form id="paymentForm" class="stack">
            <label>
                <span>Customer</span>
                <select id="payCustomerSelect" class="customer-select">
                    <option value="">Select Customer</option>
                </select>
            </label>
            <label>
                <span>Invoice (Optional)</span>
                <select id="payInvoiceSelect">
                    <option value="">Select Invoice</option>
                </select>
            </label>
            <div class="grid two">
                <label>
                    <span>Payment Date</span>
                    <input id="payDate" type="date" required value="{{ date('Y-m-d') }}">
                </label>
                <label>
                    <span>Payment Mode</span>
                    <select id="payMode">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / GPay</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </label>
            </div>
            <label>
                <span>Amount (₹)</span>
                <input id="payAmount" type="number" step="0.01" required min="0.01" placeholder="0.00">
            </label>
            <label>
                <span>Reference Number / UTR</span>
                <input id="payRef" type="text" placeholder="Transaction ID">
            </label>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button onclick="hideModal('paymentModal')" class="btn secondary" type="button">Cancel</button>
                <button class="btn primary" type="submit">Save Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function showRecordPaymentModal() {
    showModal('paymentModal');
    try {
        const [cData, iData] = await Promise.all([
            apiFetch('/customers'),
            apiFetch('/invoices')
        ]);

        const cSelect = document.getElementById('payCustomerSelect');
        cSelect.innerHTML = '<option value="">Select Customer</option>';
        cData.customers.forEach(c => {
            cSelect.innerHTML += `<option value="${c.id}">${c.name} (Due: ₹${c.balance})</option>`;
        });

        const iSelect = document.getElementById('payInvoiceSelect');
        iSelect.innerHTML = '<option value="">Select Invoice</option>';
        iData.invoices.data.forEach(inv => {
            if (inv.status !== 'paid') {
                iSelect.innerHTML += `<option value="${inv.id}">${inv.invoice_number} - ${inv.customer_name} (Total: ₹${inv.grand_total})</option>`;
            }
        });
    } catch (e) {
        console.error(e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            customer_id: document.getElementById('payCustomerSelect').value || null,
            invoice_id: document.getElementById('payInvoiceSelect').value || null,
            payment_date: document.getElementById('payDate').value,
            amount: document.getElementById('payAmount').value,
            payment_mode: document.getElementById('payMode').value,
            reference_number: document.getElementById('payRef').value,
        };

        try {
            const res = await apiFetch('/api/payments', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            if (res.success) window.location.reload();
        } catch (err) {
            alert('Failed to save payment: ' + err.message);
        }
    });
});

async function deletePayment(id) {
    if (!confirm('Are you sure you want to delete this payment record?')) return;
    try {
        const res = await apiFetch(`/api/payments/${id}`, { method: 'DELETE' });
        if (res.success) window.location.reload();
    } catch (e) {
        alert('Failed to delete payment: ' + e.message);
    }
}
</script>
@endpush
