@extends('layouts.app')

@section('title', 'Customers - GST Billing Application')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;" data-i18n="nav.customers">Customers</h2>
        <button onclick="showCustomerModal()" class="btn primary">+ Add Customer</button>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('customers.index') }}" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Customer Name, Phone, Email, GSTIN..." class="filter-search-input">
        <button class="btn secondary filter-reset-btn" type="submit" data-i18n="common.search">Search</button>
        <a href="{{ route('customers.index') }}" class="btn secondary filter-reset-btn" style="display: inline-flex; align-items: center;" data-i18n="common.reset">Reset</a>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>GSTIN</th>
                    <th>State</th>
                    <th>Outstanding Balance (₹)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td>{{ $c->phone ?? '-' }}</td>
                    <td>{{ $c->email ?? '-' }}</td>
                    <td>{{ $c->gstin ?? '-' }}</td>
                    <td>{{ $c->state ? ($c->state_code . ' - ' . $c->state) : '-' }}</td>
                    <td>
                        <strong style="color: {{ $c->balance > 0 ? 'var(--danger)' : 'var(--success)' }};">
                            ₹{{ number_format($c->balance, 2) }}
                        </strong>
                    </td>
                    <td style="display: flex; gap: 8px;">
                        <button onclick='editCustomer(@json($c))' class="btn secondary" style="padding: 4px 10px; font-size: 0.8rem;">Edit</button>
                        <button onclick="deleteCustomer({{ $c->id }})" class="btn danger" style="padding: 4px 10px; font-size: 0.8rem;">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No customers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Customer Modal -->
<div id="customerModal" class="modal-backdrop hidden">
    <div class="modal-card" style="width: min(550px, 100%);">
        <h3 id="custModalTitle" style="margin-top: 0;">Add Customer</h3>
        <form id="customerForm" class="stack">
            <input type="hidden" id="custEditId">
            <label>
                <span>Customer Name *</span>
                <input id="custName" type="text" required placeholder="Customer or Company Name">
            </label>
            <div class="grid two">
                <label>
                    <span>Phone Number</span>
                    <input id="custPhone" type="text" placeholder="9876543210">
                </label>
                <label>
                    <span>Email</span>
                    <input id="custEmail" type="email" placeholder="customer@example.com">
                </label>
            </div>
            <div class="grid two">
                <label>
                    <span>GSTIN</span>
                    <input id="custGstin" type="text" placeholder="33AAAAA0000A1Z5">
                </label>
                <label>
                    <span>State</span>
                    <select id="custState" class="state-select"></select>
                </label>
            </div>
            <label>
                <span>Address</span>
                <textarea id="custAddress" rows="2" placeholder="Full Address"></textarea>
            </label>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button onclick="hideModal('customerModal')" class="btn secondary" type="button">Cancel</button>
                <button class="btn primary" type="submit">Save Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showCustomerModal() {
    document.getElementById('custModalTitle').textContent = 'Add Customer';
    document.getElementById('custEditId').value = '';
    document.getElementById('customerForm').reset();
    showModal('customerModal');
}

function editCustomer(c) {
    document.getElementById('custModalTitle').textContent = 'Edit Customer';
    document.getElementById('custEditId').value = c.id;
    document.getElementById('custName').value = c.name;
    document.getElementById('custPhone').value = c.phone || '';
    document.getElementById('custEmail').value = c.email || '';
    document.getElementById('custGstin').value = c.gstin || '';
    document.getElementById('custAddress').value = c.address || '';
    if (c.state) document.getElementById('custState').value = c.state;
    showModal('customerModal');
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('customerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('custEditId').value;
        const stateSelect = document.getElementById('custState');
        const selectedOpt = stateSelect.options[stateSelect.selectedIndex];

        const payload = {
            name: document.getElementById('custName').value,
            phone: document.getElementById('custPhone').value,
            email: document.getElementById('custEmail').value,
            gstin: document.getElementById('custGstin').value,
            address: document.getElementById('custAddress').value,
            state: stateSelect.value,
            state_code: selectedOpt ? selectedOpt.getAttribute('data-code') : '',
        };

        try {
            const url = id ? `/api/customers/${id}` : '/api/customers';
            const method = id ? 'PUT' : 'POST';
            const res = await apiFetch(url, { method, body: JSON.stringify(payload) });
            if (res.success) window.location.reload();
        } catch (err) {
            alert('Failed to save customer: ' + err.message);
        }
    });
});

async function deleteCustomer(id) {
    if (!confirm('Are you sure you want to delete this customer?')) return;
    try {
        const res = await apiFetch(`/api/customers/${id}`, { method: 'DELETE' });
        if (res.success) window.location.reload();
    } catch (e) {
        alert('Failed to delete customer: ' + e.message);
    }
}
</script>
@endpush
