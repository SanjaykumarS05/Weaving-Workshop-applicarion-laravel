@extends('layouts.app')

@section('title', 'Settings - GST Billing Application')

@section('content')
<div class="card">
    <h2 class="card-title" data-i18n="nav.settings">Business Profile & Settings</h2>

    <!-- Language Selector Card inside Settings -->
    <div style="background: #f8fafc; border: 1px solid var(--card-border); border-radius: var(--radius-md); padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="material-symbols-outlined" style="font-size: 28px; color: #6366f1;">translate</span>
            <div>
                <strong style="font-size: 1rem; display: block;">Application Language</strong>
                <span style="font-size: 0.82rem; color: var(--text-muted);">Choose your preferred language for the interface</span>
            </div>
        </div>
        <select id="settingsLanguageSwitcher" style="width: auto; min-width: 160px; font-weight: 700; padding: 10px 14px; border-radius: 8px;">
            <option value="en">English</option>
            <option value="hi">हिन्दी</option>
            <option value="ta">தமிழ்</option>
        </select>
    </div>

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

        <hr style="border: none; border-top: 1px solid var(--card-border); margin: 12px 0;">

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

        <hr style="border: none; border-top: 1px solid var(--card-border); margin: 12px 0;">

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

<!-- Team User Management (Workspace Owner ID 1 Only) -->
@if(Auth::id() === 1)
<div class="card" style="margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <h3 style="font-size: 1.1rem; margin: 0;">Team Users & Management</h3>
            <div style="display: flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.08); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.2);">
                <span style="font-size: 0.82rem; font-weight: 700; color: #4f46e5;">Show Team Section</span>
                <label class="switch-toggle" title="Toggle Team Users section visibility">
                    <input type="checkbox" id="toggleTeamSection" checked onchange="toggleTeamSectionVisibility(this.checked)">
                    <span class="slider-round"></span>
                </label>
            </div>
        </div>
        <button id="addTeammateBtn" onclick="showModal('teamUserModal')" class="btn secondary">+ Add Teammate</button>
    </div>

    <div id="teamUserTableContainer" class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Active Toggle</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teamUsers as $u)
                <tr>
                    <td><strong>{{ $u->name }}</strong></td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <span id="userStatusBadge-{{ $u->id }}" class="badge {{ $u->active ? 'paid' : 'unpaid' }}">
                            {{ $u->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <label class="switch-toggle" title="Toggle active/inactive status">
                            <input type="checkbox" {{ $u->active ? 'checked' : '' }} {{ $u->id === Auth::id() || $u->id === 1 ? 'disabled' : '' }} onchange="toggleUserActiveStatus({{ $u->id }}, this.checked, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')">
                            <span class="slider-round"></span>
                        </label>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button onclick="openEditTeammateModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', {{ $u->active ? 1 : 0 }})" class="btn secondary sm" style="padding: 6px 12px; font-size: 0.82rem;">Edit / Reset Pass</button>
                            @if($u->id !== Auth::id() && $u->id !== 1)
                            <button onclick="deleteTeammate({{ $u->id }}, '{{ addslashes($u->name) }}')" class="btn danger sm" style="padding: 6px 12px; font-size: 0.82rem;">Delete</button>
                            @endif
                        </div>
                    </td>
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

<!-- Edit Team User Modal -->
<div id="editTeamUserModal" class="modal-backdrop hidden">
    <div class="modal-card" style="max-width: 480px;">
        <h3 style="margin-top: 0; font-size: 1.2rem;">Edit Teammate & Reset Password</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">Owner can edit member name, email, active status, or reset password.</p>
        <form id="editTeamUserForm" class="stack">
            <input type="hidden" id="editUserId">
            <label>
                <span>Full Name *</span>
                <input id="editUserName" type="text" required>
            </label>
            <label>
                <span>Email Address *</span>
                <input id="editUserEmail" type="email" required>
            </label>
            <label>
                <span>Reset Password</span>
                <input id="editUserPassword" type="password" minlength="6" placeholder="Enter new password (leave blank to keep current)">
            </label>
            <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--card-border);">
                <div>
                    <strong style="display: block; font-size: 0.9rem;">Account Active Status</strong>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Allow user to sign in to application</span>
                </div>
                <label class="switch-toggle">
                    <input type="checkbox" id="editUserActive">
                    <span class="slider-round"></span>
                </label>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button onclick="hideModal('editTeamUserModal')" class="btn secondary" type="button">Cancel</button>
                <button class="btn primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function toggleTeamSectionVisibility(visible) {
    const container = document.getElementById('teamUserTableContainer');
    const addBtn = document.getElementById('addTeammateBtn');
    if (container) container.style.display = visible ? 'block' : 'none';
    if (addBtn) addBtn.style.display = visible ? 'inline-flex' : 'none';
    localStorage.setItem('gst_show_team_users', visible ? 'true' : 'false');
}

async function toggleUserActiveStatus(userId, active, name, email) {
    try {
        const res = await apiFetch(`/api/team-user/${userId}`, {
            method: 'PUT',
            body: JSON.stringify({ name, email, active })
        });
        if (res.success) {
            const badge = document.getElementById(`userStatusBadge-${userId}`);
            if (badge) {
                badge.className = `badge ${active ? 'paid' : 'unpaid'}`;
                badge.textContent = active ? 'Active' : 'Inactive';
            }
        } else {
            alert(res.message || 'Failed to update user status.');
            window.location.reload();
        }
    } catch (err) {
        alert('Failed to update status: ' + err.message);
        window.location.reload();
    }
}

function openEditTeammateModal(id, name, email, active) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUserName').value = name;
    document.getElementById('editUserEmail').value = email;
    document.getElementById('editUserPassword').value = '';
    document.getElementById('editUserActive').checked = Boolean(active);
    showModal('editTeamUserModal');
}

async function deleteTeammate(userId, name) {
    if (!confirm(`Are you sure you want to delete teammate "${name}"?`)) return;
    try {
        const res = await apiFetch(`/api/team-user/${userId}`, {
            method: 'DELETE'
        });
        if (res.success) window.location.reload();
        else alert(res.message || 'Failed to delete teammate.');
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const langSelect = document.getElementById('settingsLanguageSwitcher');
    if (langSelect) {
        langSelect.value = localStorage.getItem('gst_app_lang') || 'en';
        langSelect.addEventListener('change', (e) => {
            setLanguage(e.target.value);
        });
    }

    const toggleTeamSec = document.getElementById('toggleTeamSection');
    if (toggleTeamSec) {
        const showSection = localStorage.getItem('gst_show_team_users') !== 'false';
        toggleTeamSec.checked = showSection;
        toggleTeamSectionVisibility(showSection);
    }

    const profStateSelect = document.getElementById('profileState');
    if (profStateSelect) {
        const selectedState = profStateSelect.getAttribute('data-selected');
        if (selectedState) profStateSelect.value = selectedState;
    }

    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const selectedOpt = profStateSelect ? profStateSelect.options[profStateSelect.selectedIndex] : null;

            const payload = {
                profile_name: document.getElementById('profileName').value,
                profile_email: document.getElementById('profileEmail').value,
                profile_gstin: document.getElementById('profileGstin').value,
                profile_phone: document.getElementById('profilePhone').value,
                profile_state: profStateSelect ? profStateSelect.value : '',
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
    }

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
                else alert(res.message || 'Failed to add teammate');
            } catch (err) {
                alert('Failed to add teammate: ' + err.message);
            }
        });
    }

    const editForm = document.getElementById('editTeamUserForm');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const userId = document.getElementById('editUserId').value;
            const payload = {
                name: document.getElementById('editUserName').value,
                email: document.getElementById('editUserEmail').value,
                active: document.getElementById('editUserActive').checked,
                password: document.getElementById('editUserPassword').value || null
            };
            try {
                const res = await apiFetch(`/api/team-user/${userId}`, {
                    method: 'PUT',
                    body: JSON.stringify(payload)
                });
                if (res.success) window.location.reload();
                else alert(res.message || 'Failed to update user');
            } catch (err) {
                alert('Error updating user: ' + err.message);
            }
        });
    }
});
</script>
@endpush
