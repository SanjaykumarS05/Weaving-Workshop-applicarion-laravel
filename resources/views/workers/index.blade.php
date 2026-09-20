@extends('layouts.app')

@section('title', 'Workers Directory - Weaving Workshop')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-color); margin: 0; display: flex; align-items: center; gap: 10px;">
            <span class="material-symbols-outlined" style="color: var(--primary-color); font-size: 28px;">engineering</span>
            Worker Directory
        </h1>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 13px;">Manage weavers and loom worker profiles</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button id="addWorkerBtn" class="btn primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
            <span class="material-symbols-outlined" style="font-size: 20px;">person_add</span>
            Add Worker
        </button>
    </div>
</div>

<!-- Search Bar -->
<div class="card" style="padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; background: var(--bg-surface); border: 1px solid var(--border-color);">
    <form method="GET" action="{{ route('workers.index') }}" autocomplete="off" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 220px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">SEARCH WORKER</label>
            <input type="text" name="search" class="form-control" autocomplete="off" value="{{ request('search') }}" placeholder="Search by name, phone number or address..." style="width: 100%;">
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn primary" style="padding: 9px 18px; font-weight: 600;">Search</button>
            <a href="{{ route('workers.index') }}" class="btn secondary" style="padding: 9px 18px; font-weight: 500; text-decoration: none;">Reset</a>
        </div>
    </form>
</div>

<!-- Workers Table Card -->
<div class="card" style="border-radius: 12px; overflow: hidden; background: var(--bg-surface); border: 1px solid var(--border-color);">
    <div style="overflow-x: auto;">
        <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--bg-hover); border-bottom: 1px solid var(--border-color); font-size: 13px; color: var(--text-muted);">
                    <th style="padding: 14px 16px; font-weight: 700; width: 60px;">#</th>
                    <th style="padding: 14px 16px; font-weight: 700;">WORKER NAME</th>
                    <th style="padding: 14px 16px; font-weight: 700;">PHONE NUMBER</th>
                    <th style="padding: 14px 16px; font-weight: 700;">ADDRESS</th>
                    <th style="padding: 14px 16px; font-weight: 700; text-align: center;">ACTIONS</th>
                </tr>
            </thead>
            <tbody style="font-size: 14px;">
                @forelse($workers as $index => $worker)
                <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s;" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 14px 16px; font-weight: 600; color: var(--text-muted);">
                        {{ $index + 1 }}
                    </td>
                    <td style="padding: 14px 16px; font-weight: 700; color: var(--text-color);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="material-symbols-outlined" style="color: #6366f1; background: rgba(99, 102, 241, 0.1); padding: 6px; border-radius: 50%; font-size: 20px;">account_circle</span>
                            <span>{{ $worker->name }}</span>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; font-weight: 600; color: var(--text-color);">
                        @if($worker->phone)
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 13px;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">call</span>
                                {{ $worker->phone }}
                            </span>
                        @else
                            <span style="color: var(--text-muted); font-weight: 400;">-</span>
                        @endif
                    </td>
                    <td style="padding: 14px 16px; color: var(--text-muted); max-width: 280px;">
                        {{ $worker->address ?? '-' }}
                    </td>
                    <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                        <a href="{{ route('borrows.index', ['worker_id' => $worker->id]) }}" class="btn icon-btn" title="View Borrow Advances Ledger" style="padding: 6px; background: rgba(99, 102, 241, 0.1); border-radius: 6px; color: #6366f1; margin-right: 6px; text-decoration: none; display: inline-flex; align-items: center;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">account_balance_wallet</span>
                        </a>
                        <button class="btn icon-btn edit-worker-btn" 
                            data-id="{{ $worker->id }}"
                            data-name="{{ $worker->name }}"
                            data-phone="{{ $worker->phone }}"
                            data-address="{{ $worker->address }}"
                            title="Edit Worker Profile" 
                            style="padding: 6px; background: transparent; border: none; color: var(--text-muted); cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                        </button>
                        <button class="btn icon-btn delete-worker-btn" 
                            data-id="{{ $worker->id }}"
                            title="Delete Worker Profile" 
                            style="padding: 6px; background: transparent; border: none; color: #ef4444; cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 36px; text-align: center; color: var(--text-muted);">
                        <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.4; display: block; margin: 0 auto 8px auto;">engineering</span>
                        No workers found. Click "Add Worker" to create your first worker entry!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Worker Modal -->
<div id="workerModal" class="modal-backdrop hidden" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7) !important; backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 9999;">
    <div class="modal-card" style="background: #ffffff !important; color: #0f172a !important; border-radius: 16px; width: 100%; max-width: 480px; padding: 28px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.4); border: 1px solid #cbd5e1; position: relative;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
            <div>
                <h3 id="workerModalTitle" style="font-size: 18px; font-weight: 800; color: #0f172a !important; margin: 0;">Add Worker Profile</h3>
                <p style="font-size: 12px; color: #64748b !important; margin: 4px 0 0 0;">Worker name, phone number & address details</p>
            </div>
            <button id="closeWorkerModalBtn" type="button" style="background: #f1f5f9; border: none; color: #64748b; cursor: pointer; padding: 6px 8px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
            </button>
        </div>

        <form id="workerForm" autocomplete="off">
            <input type="hidden" id="workerId" value="">
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    WORKER NAME <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" id="workerName" class="form-control" autocomplete="new-password" required style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;" placeholder="Enter worker full name (e.g. Ram)">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    PHONE NUMBER
                </label>
                <input type="text" id="workerPhone" class="form-control" autocomplete="new-password" style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;" placeholder="Enter mobile number (e.g. 9876543210)">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    ADDRESS
                </label>
                <textarea id="workerAddress" class="form-control" rows="3" autocomplete="off" style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;" placeholder="Worker street address, village, or loom shed detail..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                <button type="button" id="cancelWorkerModalBtn" class="btn secondary" style="background: #f1f5f9; color: #475569; font-weight: 600; padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1;">Cancel</button>
                <button type="submit" id="saveWorkerModalBtn" class="btn primary" style="background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%); color: #ffffff; font-weight: 700; padding: 10px 22px; border-radius: 8px; border: none; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">Save Worker</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('workerModal');
    const addBtn = document.getElementById('addWorkerBtn');
    const closeBtn = document.getElementById('closeWorkerModalBtn');
    const cancelBtn = document.getElementById('cancelWorkerModalBtn');
    const form = document.getElementById('workerForm');
    const modalTitle = document.getElementById('workerModalTitle');

    const workerId = document.getElementById('workerId');
    const workerName = document.getElementById('workerName');
    const workerPhone = document.getElementById('workerPhone');
    const workerAddress = document.getElementById('workerAddress');

    const openModal = (editData = null) => {
        if (editData) {
            modalTitle.textContent = 'Edit Worker Profile';
            workerId.value = editData.id;
            workerName.value = editData.name;
            workerPhone.value = editData.phone || '';
            workerAddress.value = editData.address || '';
        } else {
            modalTitle.textContent = 'Add Worker Profile';
            form.reset();
            workerId.value = '';
        }
        modal.classList.remove('hidden');
        if (typeof disableAutofill === 'function') disableAutofill();
    };

    const closeModal = () => {
        modal.classList.add('hidden');
    };

    if (addBtn) addBtn.addEventListener('click', () => openModal());
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.querySelectorAll('.edit-worker-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal({
                id: btn.dataset.id,
                name: btn.dataset.name,
                phone: btn.dataset.phone,
                address: btn.dataset.address,
            });
        });
    });

    document.querySelectorAll('.delete-worker-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to delete this worker? Borrow records associated with this worker will also be deleted.')) return;
            const id = btn.dataset.id;
            try {
                const response = await fetch(`${window.APP_URL}/api/workers/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const res = await response.json();
                if (res.success) {
                    showToast('Worker profile deleted successfully!', 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(res.message || 'Error deleting worker profile.', 'error');
                }
            } catch (err) {
                showToast('Network error while deleting worker.', 'error');
            }
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = workerId.value;
        const payload = {
            name: workerName.value.trim(),
            phone: workerPhone.value.trim(),
            address: workerAddress.value.trim(),
        };

        const url = id ? `${window.APP_URL}/api/workers/${id}` : `${window.APP_URL}/api/workers`;
        const method = id ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const res = await response.json();
            if (res.success) {
                closeModal();
                showToast(res.message || 'Worker profile saved successfully!', 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(res.message || 'Error saving worker profile.', 'error');
            }
        } catch (err) {
            showToast('Network error saving worker profile.', 'error');
        }
    });
});
</script>
@endpush
