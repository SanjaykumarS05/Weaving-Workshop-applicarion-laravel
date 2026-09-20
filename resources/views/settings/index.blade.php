@extends('layouts.app')

@section('title', 'Settings - GST Billing Application')

@section('content')
<div class="card">
    <h2 class="card-title" data-i18n="nav.settings">Business Profile & Settings</h2>

    <form id="settingsForm" class="stack">
        <h3 style="font-size: 1.05rem; margin-top: 8px;">Company Information</h3>
        <div class="grid two">
            <label>
                <span>Company / Business Profile Name</span>
                <input id="profileName" type="text" value="{{ $setting->profile_name }}">
            </label>
            <label>
                <span>Business GSTIN</span>
                <input id="profileGstin" type="text" value="{{ $setting->profile_gstin }}" placeholder="33AAAAA0000A1Z5">
            </label>
        </div>

        <div class="grid three">
            <label>
                <span>Business Phone</span>
                <input id="profilePhone" type="text" value="{{ $setting->profile_phone }}">
            </label>
            <label>
                <span>Business Email</span>
                <input id="profileEmail" type="email" value="{{ $setting->profile_email }}">
            </label>
            <label>
                <span>State</span>
                <select id="profileState" class="state-select" data-selected="{{ $setting->profile_state }}"></select>
            </label>
        </div>

        <label>
            <span>Full Address</span>
            <textarea id="profileAddress" rows="2">{{ $setting->profile_address }}</textarea>
        </label>

        <hr style="border: none; border-top: 1px solid var(--line); margin: 12px 0;">

        <h3 style="font-size: 1.05rem;">Bank Account Details</h3>
        <div class="grid two">
            <label>
                <span>Bank Name</span>
                <input id="profileBankName" type="text" value="{{ $setting->profile_bank_name }}" placeholder="State Bank of India">
            </label>
            <label>
                <span>Account Number</span>
                <input id="profileAccountNo" type="text" value="{{ $setting->profile_account_no }}">
            </label>
        </div>
        <div class="grid two">
            <label>
                <span>Branch Name</span>
                <input id="profileBranchName" type="text" value="{{ $setting->profile_branch_name }}">
            </label>
            <label>
                <span>IFSC Code</span>
                <input id="profileIfsc" type="text" value="{{ $setting->profile_ifsc }}" placeholder="SBIN0001234">
            </label>
        </div>

        <hr style="border: none; border-top: 1px solid var(--line); margin: 12px 0;">

        <h3 style="font-size: 1.05rem;">Invoice & Tax Defaults</h3>
        <div class="grid three">
            <label>
                <span>Invoice Prefix</span>
                <input id="invoicePrefix" type="text" value="{{ $setting->invoice_prefix }}">
            </label>
            <label>
                <span>Invoice Start Number</span>
                <input id="invoiceStartValue" type="number" value="{{ $setting->invoice_start_value }}">
            </label>
            <label>
                <span>Default Tax Rate (%)</span>
                <input id="defaultTaxRate" type="number" step="0.01" value="{{ $setting->default_tax_rate }}">
            </label>
        </div>

        <label>
            <span>Invoice Declaration</span>
            <textarea id="profileDeclaration" rows="2">{{ $setting->profile_declaration }}</textarea>
        </label>
        <label>
            <span>Signatory Title</span>
            <input id="profileSignatory" type="text" value="{{ $setting->profile_signatory }}">
        </label>

        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
            <button class="btn primary" type="submit">Save Business Profile</button>
        </div>
    </form>
</div>

<!-- Team User Management (Admin Only) -->
@if(Auth::user()->role === 'admin')
<div class="card" style="margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="font-size: 1.1rem; margin: 0;">Team Users</h3>
        <button onclick="showModal('teamUserModal')" class="btn secondary">+ Add Teammate</button>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teamUsers as $u)
                <tr>
                    <td><strong>{{ $u->name }}</strong></td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge secondary">{{ strtoupper($u->role) }}</span></td>
                    <td><span class="badge {{ $u->active ? 'paid' : 'unpaid' }}">{{ $u->active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Team User Modal -->
<div id="teamUserModal" class="modal-backdrop hidden">
    <div class="modal-card">
        <h3 style="margin-top: 0;">Add Team Member</h3>
        <form id="teamUserForm" class="stack">
            <label>
                <span>Name *</span>
                <input id="teamUsername" type="text" required>
            </label>
            <label>
                <span>Email *</span>
                <input id="teamEmail" type="email" required placeholder="teammate@example.com">
            </label>
            <label>
                <span>Password *</span>
                <input id="teamPassword" type="password" required minlength="6">
            </label>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button onclick="hideModal('teamUserModal')" class="btn secondary" type="button">Cancel</button>
                <button class="btn primary" type="submit">Add User</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const profStateSelect = document.getElementById('profileState');
    const selectedState = profStateSelect.getAttribute('data-selected');
    if (selectedState) profStateSelect.value = selectedState;

    document.getElementById('settingsForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const selectedOpt = profStateSelect.options[profStateSelect.selectedIndex];

        const payload = {
            profile_name: document.getElementById('profileName').value,
            profile_email: document.getElementById('profileEmail').value,
            profile_gstin: document.getElementById('profileGstin').value,
            profile_phone: document.getElementById('profilePhone').value,
            profile_state: profStateSelect.value,
            profile_state_code: selectedOpt ? selectedOpt.getAttribute('data-code') : '',
            profile_address: document.getElementById('profileAddress').value,
            profile_bank_name: document.getElementById('profileBankName').value,
            profile_account_no: document.getElementById('profileAccountNo').value,
            profile_branch_name: document.getElementById('profileBranchName').value,
            profile_ifsc: document.getElementById('profileIfsc').value,
            profile_declaration: document.getElementById('profileDeclaration').value,
            profile_signatory: document.getElementById('profileSignatory').value,
            default_tax_rate: document.getElementById('defaultTaxRate').value,
            invoice_prefix: document.getElementById('invoicePrefix').value,
            invoice_start_value: document.getElementById('invoiceStartValue').value,
        };

        try {
            const res = await apiFetch('/api/settings', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            if (res.success) alert('Business settings updated successfully!');
        } catch (err) {
            alert('Failed to update settings: ' + err.message);
        }
    });

    const teamForm = document.getElementById('teamUserForm');
    if (teamForm) {
        teamForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                username: document.getElementById('teamUsername').value,
                email: document.getElementById('teamEmail').value,
                password: document.getElementById('teamPassword').value,
            };
            try {
                const res = await apiFetch('/api/team-user', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });
                if (res.success) window.location.reload();
            } catch (err) {
                alert('Failed to add teammate: ' + err.message);
            }
        });
    }
});
</script>
@endpush
