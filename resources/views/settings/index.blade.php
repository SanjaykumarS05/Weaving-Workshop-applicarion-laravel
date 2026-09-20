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
            <button id="saveBusinessProfileBtn" class="btn primary" type="submit" disabled style="opacity: 0.5; cursor: not-allowed; transition: all 0.2s ease;">Save Business Profile</button>
        </div>
    </form>
</div>

<!-- Registered Company Accounts & User Management (System Owner ID 1 Only) -->
@if(Auth::id() === 1)
<div class="card" style="margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <h3 style="font-size: 1.1rem; margin: 0;">User Accounts & Company Access</h3>
            <div style="display: flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.08); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.2);">
                <span style="font-size: 0.82rem; font-weight: 700; color: #4f46e5;">Show Users Section</span>
                <label class="switch-toggle" title="Toggle User Accounts section visibility">
                    <input type="checkbox" id="toggleTeamSection" checked onchange="toggleTeamSectionVisibility(this.checked)">
                    <span class="slider-round"></span>
                </label>
            </div>
        </div>
        <button id="addTeammateBtn" onclick="showModal('teamUserModal')" class="btn secondary">+ Add Company User</button>
    </div>

    <div id="teamUserTableContainer" class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Account Active Status</th>
                    <th>Active Toggle</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teamUsers as $u)
                <tr>
                    <td><strong>{{ $u->business_name ?? $u->name }}</strong></td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <span id="userStatusBadge-{{ $u->id }}" class="badge {{ $u->active ? 'paid' : 'unpaid' }}">
                            {{ $u->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <label class="switch-toggle" title="Toggle active/inactive access">
                            <input type="checkbox" {{ $u->active ? 'checked' : '' }} onchange="toggleUserActiveStatus({{ $u->id }}, this.checked, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ addslashes($u->business_name ?? $u->name) }}')">
                            <span class="slider-round"></span>
                        </label>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button onclick="openEditTeammateModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->business_name ?? $u->name) }}', '{{ addslashes($u->email) }}', {{ $u->active ? 1 : 0 }}, {{ json_encode($u->allowed_navs ?? []) }})" class="btn secondary sm" style="padding: 6px 12px; font-size: 0.82rem;">Edit / Permissions</button>
                            <button onclick="deleteTeammate({{ $u->id }}, '{{ addslashes($u->name) }}')" class="btn danger sm" style="padding: 6px 12px; font-size: 0.82rem;">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 28px;">
                        No other company user accounts registered yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Company User Modal -->
<div id="teamUserModal" class="modal-backdrop hidden">
    <div class="modal-card">
        <h3 style="margin-top: 0;">Add Company User Account</h3>
        <form id="teamUserForm" class="stack">
            <label>
                <span>Company / Business Name *</span>
                <input id="teamBusinessName" type="text" required placeholder="e.g. Acme Weaving Mills">
            </label>
            <label>
                <span>User Name *</span>
                <input id="teamUsername" type="text" required placeholder="User contact name">
            </label>
            <label>
                <span>Email Address *</span>
                <input id="teamEmail" type="email" required placeholder="user@company.com">
            </label>
            <label>
                <span>Password *</span>
                <input id="teamPassword" type="password" required minlength="6">
            </label>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button onclick="hideModal('teamUserModal')" class="btn secondary" type="button">Cancel</button>
                <button class="btn primary" type="submit">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Company User Modal with 2 Tabs -->
<div id="editTeamUserModal" class="modal-backdrop hidden">
    <div class="modal-card" style="max-width: 520px; border-radius: 16px;">
        <h3 style="margin-top: 0; font-size: 1.2rem; font-weight: 800; color: var(--text-color);">Edit User Account & Permissions</h3>
        
        <!-- Tab Navigation Buttons -->
        <div style="display: flex; gap: 6px; background: #f1f5f9; padding: 4px; border-radius: 10px; margin-bottom: 20px;">
            <button type="button" id="tabBtnAccount" onclick="switchEditUserTab('account')" style="flex: 1; border-radius: 8px; font-weight: 700; font-size: 0.85rem; padding: 9px 12px; background: #ffffff; color: #4f46e5; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px;">person</span>
                1. Account Details
            </button>
            <button type="button" id="tabBtnNavs" onclick="switchEditUserTab('navs')" style="flex: 1; border-radius: 8px; font-weight: 600; font-size: 0.85rem; padding: 9px 12px; background: transparent; color: #64748b; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px;">checklist</span>
                2. Navigation Permissions
            </button>
        </div>

        <form id="editTeamUserForm" class="stack">
            <input type="hidden" id="editUserId">

            <!-- Tab 1: Account Information -->
            <div id="tabContentAccount" class="tab-pane">
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0 0 14px 0;">Edit company name, user contact, email, reset password, or active status.</p>
                <div class="stack">
                    <label>
                        <span>Company / Business Name</span>
                        <input id="editUserBusinessName" type="text">
                    </label>
                    <label>
                        <span>User Contact Name *</span>
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
                </div>
            </div>

            <!-- Tab 2: Navigation Permissions Checklist -->
            <div id="tabContentNavs" class="tab-pane hidden">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <strong style="font-size: 0.9rem; display: block; color: var(--text-color);">Sidebar Navigation Access</strong>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Checked navigation bars will be visible to this user</span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button type="button" onclick="selectAllUserNavs(true)" class="btn secondary sm" style="padding: 4px 10px; font-size: 0.78rem;">Select All</button>
                        <button type="button" onclick="selectAllUserNavs(false)" class="btn secondary sm" style="padding: 4px 10px; font-size: 0.78rem;">Clear All</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-height: 280px; overflow-y: auto; padding: 6px; border: 1px solid var(--card-border); border-radius: 8px; background: #f8fafc;">
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="dashboard" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #6366f1;">dashboard</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Dashboard</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="billing" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #10b981;">receipt_long</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Billing</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="invoices" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">description</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Invoices</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="delivery-sheets" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #f59e0b;">local_shipping</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Delivery Sheet</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="payments" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #8b5cf6;">payments</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Payments</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="customers" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #ec4899;">groups</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Customers</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="products" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #14b8a6;">inventory_2</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Products</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="product-sales" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #06b6d4;">monitoring</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Product Sales</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="stock-register" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #f97316;">swap_vert</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Stock Register</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="looms" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #84cc16;">precision_manufacturing</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Looms</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="workers" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #6366f1;">engineering</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Worker</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="edit-nav-checkbox" value="borrows" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #a855f7;">account_balance_wallet</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Borrow</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 6px; background: #ffffff; border: 1px solid #e2e8f0; cursor: pointer; margin: 0; grid-column: span 2;">
                        <input type="checkbox" class="edit-nav-checkbox" value="settings" style="width: 16px; height: 16px; accent-color: #6366f1;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #64748b;">settings</span>
                        <span style="font-size: 0.85rem; font-weight: 600;">Settings</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid var(--card-border); padding-top: 14px;">
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
function switchEditUserTab(tabName) {
    const btnAcc = document.getElementById('tabBtnAccount');
    const btnNav = document.getElementById('tabBtnNavs');
    const contentAcc = document.getElementById('tabContentAccount');
    const contentNav = document.getElementById('tabContentNavs');

    if (tabName === 'account') {
        btnAcc.style.background = '#ffffff';
        btnAcc.style.color = '#4f46e5';
        btnAcc.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        btnAcc.style.fontWeight = '700';

        btnNav.style.background = 'transparent';
        btnNav.style.color = '#64748b';
        btnNav.style.boxShadow = 'none';
        btnNav.style.fontWeight = '600';

        contentAcc.classList.remove('hidden');
        contentNav.classList.add('hidden');
    } else {
        btnNav.style.background = '#ffffff';
        btnNav.style.color = '#4f46e5';
        btnNav.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        btnNav.style.fontWeight = '700';

        btnAcc.style.background = 'transparent';
        btnAcc.style.color = '#64748b';
        btnAcc.style.boxShadow = 'none';
        btnAcc.style.fontWeight = '600';

        contentNav.classList.remove('hidden');
        contentAcc.classList.add('hidden');
    }
}

function selectAllUserNavs(checked) {
    document.querySelectorAll('.edit-nav-checkbox').forEach(cb => {
        cb.checked = Boolean(checked);
    });
}

function toggleTeamSectionVisibility(visible) {
    const container = document.getElementById('teamUserTableContainer');
    const addBtn = document.getElementById('addTeammateBtn');
    if (container) container.style.display = visible ? 'block' : 'none';
    if (addBtn) addBtn.style.display = visible ? 'inline-flex' : 'none';
    localStorage.setItem('gst_show_team_users', visible ? 'true' : 'false');
}

async function toggleUserActiveStatus(userId, active, name, email, bizName) {
    try {
        const res = await apiFetch(`/api/team-user/${userId}`, {
            method: 'PUT',
            body: JSON.stringify({ name, email, active, business_name: bizName })
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

function openEditTeammateModal(id, name, bizName, email, active, allowedNavs = []) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUserName').value = name;
    document.getElementById('editUserBusinessName').value = bizName || name;
    document.getElementById('editUserEmail').value = email;
    document.getElementById('editUserPassword').value = '';
    document.getElementById('editUserActive').checked = Boolean(active);

    switchEditUserTab('account');

    const navArray = Array.isArray(allowedNavs) ? allowedNavs : [];
    document.querySelectorAll('.edit-nav-checkbox').forEach(cb => {
        if (navArray.length === 0) {
            cb.checked = true;
        } else {
            cb.checked = navArray.includes(cb.value);
        }
    });

    showModal('editTeamUserModal');
}

async function deleteTeammate(userId, name) {
    if (!confirm(`Are you sure you want to delete user account "${name}"?`)) return;
    try {
        const res = await apiFetch(`/api/team-user/${userId}`, {
            method: 'DELETE'
        });
        if (res.success) window.location.reload();
        else alert(res.message || 'Failed to delete user.');
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
    const saveProfileBtn = document.getElementById('saveBusinessProfileBtn');

    if (settingsForm && saveProfileBtn) {
        const enableSaveBtn = () => {
            saveProfileBtn.disabled = false;
            saveProfileBtn.style.opacity = '1';
            saveProfileBtn.style.cursor = 'pointer';
        };

        settingsForm.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', enableSaveBtn);
            field.addEventListener('change', enableSaveBtn);
        });

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
                if (res.success) {
                    showToast('Business settings updated successfully!', 'success');
                    saveProfileBtn.disabled = true;
                    saveProfileBtn.style.opacity = '0.5';
                    saveProfileBtn.style.cursor = 'not-allowed';
                }
            } catch (err) {
                showToast('Failed to update settings: ' + err.message, 'error');
            }
        });
    }

    const teamForm = document.getElementById('teamUserForm');
    if (teamForm) {
        teamForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                business_name: document.getElementById('teamBusinessName').value,
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
                else alert(res.message || 'Failed to add user account');
            } catch (err) {
                alert('Failed to add user account: ' + err.message);
            }
        });
    }

    const editForm = document.getElementById('editTeamUserForm');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const userId = document.getElementById('editUserId').value;
            const selectedNavs = Array.from(document.querySelectorAll('.edit-nav-checkbox:checked')).map(cb => cb.value);

            const payload = {
                business_name: document.getElementById('editUserBusinessName').value,
                name: document.getElementById('editUserName').value,
                email: document.getElementById('editUserEmail').value,
                active: document.getElementById('editUserActive').checked,
                password: document.getElementById('editUserPassword').value || null,
                allowed_navs: selectedNavs
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
