@extends('layouts.app')

@section('title', 'Invoices - GST Billing Application')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;" data-i18n="nav.invoices">Invoices</h2>
        <a href="{{ route('billing') }}" class="btn primary" data-i18n="billing.createInvoice">+ Create Invoice</a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('invoices.index') }}" style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Invoice No, Customer, Phone..." style="flex: 1; min-width: 200px;">
        <input type="date" name="from_date" value="{{ request('from_date') }}">
        <input type="date" name="to_date" value="{{ request('to_date') }}">
        <select name="status">
            <option value="all">All Status</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
        </select>
        <button class="btn secondary" type="submit" data-i18n="common.search">Search</button>
        <a href="{{ route('invoices.index') }}" class="btn ghost" data-i18n="common.reset">Reset</a>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Supply Type</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td><strong>{{ $inv->invoice_number }}</strong></td>
                    <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td>
                        <div><strong>{{ $inv->customer_name }}</strong></div>
                        <small style="color: var(--muted);">{{ $inv->customer_phone }}</small>
                    </td>
                    <td><span class="badge secondary">{{ strtoupper($inv->supply_type) }}</span></td>
                    <td>₹{{ number_format($inv->grand_total, 2) }}</td>
                    <td>₹{{ number_format($inv->paid_amount, 2) }}</td>
                    <td><span class="badge {{ $inv->status }}">{{ $inv->status }}</span></td>
                    <td style="display: flex; gap: 8px;">
                        <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="btn secondary" style="padding: 4px 10px; font-size: 0.8rem;">Print</a>
                        <button onclick="deleteInvoice({{ $inv->id }})" class="btn danger" type="button" style="padding: 4px 10px; font-size: 0.8rem;">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--muted); padding: 24px;">No invoices found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $invoices->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
async function deleteInvoice(id) {
    if (!confirm('Are you sure you want to delete this invoice? Product stock and customer balance will be restored.')) return;
    try {
        const res = await apiFetch(`/api/invoices/${id}`, { method: 'DELETE' });
        if (res.success) {
            window.location.reload();
        }
    } catch (e) {
        alert('Failed to delete invoice: ' + e.message);
    }
}
</script>
@endpush
