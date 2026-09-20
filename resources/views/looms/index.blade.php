@extends('layouts.app')

@section('title', 'Heavy Looms - Warp & Woven Cloth Production Ledger')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-color); margin: 0; display: flex; align-items: center; gap: 10px;">
            <span class="material-symbols-outlined" style="color: var(--primary-color); font-size: 28px;">precision_manufacturing</span>
            Heavy Looms
        </h1>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 13px;">Owner ledger tracking warp beams issued to heavy looms and woven cloth received back from workers.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button id="openExportBtn" class="btn secondary" title="Export / Download Report" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; padding: 0; border-radius: 8px; background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-color); cursor: pointer;">
            <span class="material-symbols-outlined" style="font-size: 22px; color: #6366f1;">download</span>
        </button>
        <button id="addEntryBtn" class="btn primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
            <span class="material-symbols-outlined" style="font-size: 20px;">add_circle</span>
            Add Loom Entry
        </button>
    </div>
</div>

<!-- Summary Metric Cards -->
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card metric-card" style="padding: 18px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Given to Loom</span>
            <span class="material-symbols-outlined" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1); padding: 6px; border-radius: 8px;">arrow_upward</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #f59e0b; margin-top: 10px;" id="metricQtyIn">
            {{ number_format($totalQtyIn, 3) }} <span style="font-size: 14px; font-weight: 600;">m</span>
        </div>
    </div>

    <div class="card metric-card" style="padding: 18px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Cloth Received from Worker</span>
            <span class="material-symbols-outlined" style="color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 6px; border-radius: 8px;">arrow_downward</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 10px;" id="metricQtyOut">
            {{ number_format($totalQtyOut, 3) }} <span style="font-size: 14px; font-weight: 600;">m</span>
        </div>
    </div>

    <div class="card metric-card" style="padding: 18px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Remaining Warp Balance</span>
            <span class="material-symbols-outlined" style="color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 6px; border-radius: 8px;">account_balance_wallet</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #3b82f6; margin-top: 10px;" id="metricBalance">
            {{ number_format(abs($currentBalance), 3) }} <span style="font-size: 14px; font-weight: 600;">m</span>
        </div>
    </div>

    <div class="card metric-card" style="padding: 18px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Loom Entries</span>
            <span class="material-symbols-outlined" style="color: #6366f1; background: rgba(99, 102, 241, 0.1); padding: 6px; border-radius: 8px;">receipt_long</span>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-color); margin-top: 10px;">
            {{ $entries->total() }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">records</span>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; background: var(--bg-surface); border: 1px solid var(--border-color);">
    <form method="GET" action="{{ route('looms.index') }}" autocomplete="off" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div style="min-width: 170px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">SELECT WORKER</label>
            <select name="worker_id" class="form-control" style="width: 100%;">
                <option value="">All Workers</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}" {{ request('worker_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">SEARCH DETAILS / NOTES</label>
            <input type="text" name="search" class="form-control" autocomplete="off" value="{{ request('search') }}" placeholder="Search by warp details, specs, or notes..." style="width: 100%;">
        </div>

        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">ENTRY TYPE</label>
            <select name="type" class="form-control" style="width: 100%;">
                <option value="">All Types</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Given to Loom</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Cloth Received from Worker</option>
            </select>
        </div>

        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">FROM DATE</label>
            <input type="date" name="from_date" class="form-control" autocomplete="off" value="{{ request('from_date') }}" style="width: 100%;">
        </div>

        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">TO DATE</label>
            <input type="date" name="to_date" class="form-control" autocomplete="off" value="{{ request('to_date') }}" style="width: 100%;">
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn primary" style="padding: 9px 16px; font-weight: 600;">Filter</button>
            <a href="{{ route('looms.index') }}" class="btn secondary" style="padding: 9px 16px; font-weight: 500; text-decoration: none;">Reset</a>
        </div>
    </form>
</div>

<!-- Main Loom Ledger Table -->
<div class="card" style="border-radius: 12px; overflow: hidden; background: var(--bg-surface); border: 1px solid var(--border-color);">
    <div style="overflow-x: auto;">
        <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--bg-hover); border-bottom: 1px solid var(--border-color); font-size: 13px; color: var(--text-muted);">
                    <th style="padding: 14px 16px; font-weight: 700;">DATE</th>
                    <th style="padding: 14px 16px; font-weight: 700;">WORKER NAME</th>
                    <th style="padding: 14px 16px; font-weight: 700;">WARP / PRODUCTION DETAILS</th>
                    <th style="padding: 14px 16px; font-weight: 700; text-align: right; color: #f59e0b;">GIVEN TO LOOM</th>
                    <th style="padding: 14px 16px; font-weight: 700; text-align: right; color: #10b981;">CLOTH RECEIVED FROM WORKER</th>
                    <th style="padding: 14px 16px; font-weight: 700; text-align: right; color: #3b82f6;">REMAINING WARP BALANCE</th>
                    <th style="padding: 14px 16px; font-weight: 700;">NOTES</th>
                    <th style="padding: 14px 16px; font-weight: 700; text-align: center;">ACTIONS</th>
                </tr>
            </thead>
            <tbody style="font-size: 14px;">
                @forelse($entries as $entry)
                <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s;" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px 16px; font-weight: 600; white-space: nowrap; color: var(--text-color);">
                        {{ \Carbon\Carbon::parse($entry->entry_date)->format('d/m/Y') }}
                    </td>
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--text-color);">
                        @if($entry->worker)
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 700;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">engineering</span>
                                {{ $entry->worker->name }}
                            </span>
                        @else
                            <span style="color: var(--text-muted); font-weight: 400;">-</span>
                        @endif
                    </td>
                    <td style="padding: 12px 16px; font-weight: 700; color: var(--text-color);">
                        <span style="background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 3px 8px; border-radius: 6px; font-size: 13px;">
                            {{ $entry->details ?? '-' }}
                        </span>
                    </td>
                    <td style="padding: 12px 16px; text-align: right; font-weight: 700; color: #f59e0b;">
                        @if($entry->qty_in > 0)
                            {{ number_format($entry->qty_in, 3) }} m
                        @else
                            <span style="color: var(--text-muted); font-weight: 400;">-</span>
                        @endif
                    </td>
                    <td style="padding: 12px 16px; text-align: right; font-weight: 700; color: #10b981;">
                        @if($entry->qty_out > 0)
                            {{ number_format($entry->qty_out, 3) }} m
                        @else
                            <span style="color: var(--text-muted); font-weight: 400;">-</span>
                        @endif
                    </td>
                    <td style="padding: 12px 16px; text-align: right; font-weight: 800; color: #3b82f6;">
                        {{ number_format(abs($entry->calc_balance ?? 0), 3) }} m
                    </td>
                    <td style="padding: 12px 16px; color: var(--text-muted); font-size: 13px;">
                        {{ $entry->notes ?? '-' }}
                    </td>
                    <td style="padding: 12px 16px; text-align: center; white-space: nowrap;">
                        <button class="btn icon-btn edit-entry-btn" 
                            data-id="{{ $entry->id }}"
                            data-worker-id="{{ $entry->worker_id }}"
                            data-date="{{ $entry->entry_date }}"
                            data-type="{{ $entry->type }}"
                            data-qty="{{ $entry->type === 'in' ? $entry->qty_in : $entry->qty_out }}"
                            data-details="{{ $entry->details }}"
                            data-notes="{{ $entry->notes }}"
                            title="Edit Record" 
                            style="padding: 6px; background: transparent; border: none; color: var(--text-muted); cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                        </button>
                        <button class="btn icon-btn delete-entry-btn" 
                            data-id="{{ $entry->id }}"
                            title="Delete Record" 
                            style="padding: 6px; background: transparent; border: none; color: #ef4444; cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 32px; text-align: center; color: var(--text-muted);">
                        <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.4; display: block; margin: 0 auto 8px auto;">precision_manufacturing</span>
                        No loom ledger records found. Click "Add Loom Entry" to record your warp production!
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($entries->total() > 0)
            <tfoot>
                <tr style="background: var(--bg-hover); font-weight: 800; border-top: 2px solid var(--border-color);">
                    <td colspan="3" style="padding: 14px 16px; color: var(--text-color);">ALL-TIME OVERALL TOTALS:</td>
                    <td style="padding: 14px 16px; text-align: right; color: #f59e0b; font-size: 15px;">
                        {{ number_format($totalQtyIn, 3) }} m
                    </td>
                    <td style="padding: 14px 16px; text-align: right; color: #10b981; font-size: 15px;">
                        {{ number_format($totalQtyOut, 3) }} m
                    </td>
                    <td style="padding: 14px 16px; text-align: right; color: #3b82f6; font-size: 15px;">
                        {{ number_format(abs($currentBalance), 3) }} m
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($entries->hasPages())
    <div style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: 12px; background: var(--bg-surface);">
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
            Showing <strong>{{ $entries->firstItem() }}</strong> to <strong>{{ $entries->lastItem() }}</strong> of <strong>{{ $entries->total() }}</strong> loom entries
        </div>
        <div class="custom-pagination" style="display: flex; gap: 6px; align-items: center;">
            @if ($entries->onFirstPage())
                <span style="padding: 6px 12px; border-radius: 6px; background: var(--bg-hover); color: var(--text-muted); opacity: 0.5; font-size: 13px; cursor: not-allowed; font-weight: 600;">&laquo; Previous</span>
            @else
                <a href="{{ $entries->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--bg-hover); color: var(--text-color); font-size: 13px; text-decoration: none; font-weight: 600;">&laquo; Previous</a>
            @endif

            @foreach ($entries->getUrlRange(1, $entries->lastPage()) as $page => $url)
                @if ($page == $entries->currentPage())
                    <span style="padding: 6px 12px; border-radius: 6px; background: var(--primary-color, #6366f1); color: #ffffff; font-size: 13px; font-weight: 700;">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" style="padding: 6px 12px; border-radius: 6px; background: var(--bg-hover); color: var(--text-color); font-size: 13px; text-decoration: none; font-weight: 600;">{{ $page }}</a>
                @endif
            @endforeach

            @if ($entries->hasMorePages())
                <a href="{{ $entries->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--bg-hover); color: var(--text-color); font-size: 13px; text-decoration: none; font-weight: 600;">Next &raquo;</a>
            @else
                <span style="padding: 6px 12px; border-radius: 6px; background: var(--bg-hover); color: var(--text-muted); opacity: 0.5; font-size: 13px; cursor: not-allowed; font-weight: 600;">Next &raquo;</span>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- High-Contrast Fully Opaque Add / Edit Loom Entry Modal -->
<div id="loomEntryModal" class="modal-backdrop hidden" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7) !important; backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 9999;">
    <div class="modal-card" style="background: #ffffff !important; color: #0f172a !important; border-radius: 16px; width: 100%; max-width: 540px; padding: 28px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.4); border: 1px solid #cbd5e1; position: relative;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
            <div>
                <h3 id="modalTitle" style="font-size: 18px; font-weight: 800; color: #0f172a !important; margin: 0;">Add Loom Register Entry</h3>
                <p style="font-size: 12px; color: #64748b !important; margin: 4px 0 0 0;">Enter warp beam issued to loom or cloth received from worker</p>
            </div>
            <button id="closeModalBtn" type="button" style="background: #f1f5f9; border: none; color: #64748b; cursor: pointer; padding: 6px 8px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
            </button>
        </div>

        <form id="loomEntryForm" autocomplete="off">
            <input type="hidden" id="entryId" value="">
            
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    SELECT WORKER
                </label>
                <select id="workerId" class="form-control" style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;">
                    <option value="">Select Worker (Optional)</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} {{ $w->phone ? '('.$w->phone.')' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                        ENTRY DATE <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="entryDate" class="form-control" autocomplete="off" required style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;" value="{{ date('Y-m-d') }}">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                        ENTRY TYPE <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="entryType" class="form-control" required style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;">
                        <option value="in">Given to Loom</option>
                        <option value="out">Cloth Received from Worker</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    QUANTITY IN METERS <span style="color: #ef4444;">*</span>
                </label>
                <input type="number" step="0.001" min="0.001" id="entryQuantity" class="form-control" autocomplete="off" required style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;" placeholder="e.g. 2100 or 386">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    WARP / PRODUCTION DETAILS
                </label>
                <input type="text" id="entryDetails" class="form-control" autocomplete="off" style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;" placeholder="e.g. 4000 E 2 Warp, 170 x 1.90, 203 x 1.90">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    NOTES / REMARKS
                </label>
                <input type="text" id="entryNotes" class="form-control" autocomplete="off" style="width: 100%; background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 500;" placeholder="e.g. Warp loaded on loom, cloth woven">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                <button type="button" id="cancelModalBtn" class="btn secondary" style="background: #f1f5f9; color: #475569; font-weight: 600; padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1;">Cancel</button>
                <button type="submit" id="saveModalBtn" class="btn primary" style="background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%); color: #ffffff; font-weight: 700; padding: 10px 22px; border-radius: 8px; border: none; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">Save Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- Export Format Selection Modal -->
<div id="exportModal" class="modal-backdrop hidden" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7) !important; backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 9999;">
    <div class="modal-card" style="background: #ffffff !important; color: #0f172a !important; border-radius: 16px; width: 100%; max-width: 440px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.4); border: 1px solid #cbd5e1; position: relative;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a !important; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="color: #6366f1;">download</span>
                    Export Heavy Looms Ledger
                </h3>
                <p style="font-size: 12px; color: #64748b !important; margin: 4px 0 0 0;">Download report based on current search & filter</p>
            </div>
            <button id="closeExportModalBtn" type="button" style="background: #f1f5f9; border: none; color: #64748b; cursor: pointer; padding: 6px 8px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
            </button>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: #334155 !important; margin-bottom: 10px; letter-spacing: 0.5px; text-transform: uppercase;">
                SELECT EXPORT FORMAT
            </label>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label class="export-option-card" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 12px; padding: 12px 14px; border: 1.5px solid #6366f1; border-radius: 10px; cursor: pointer; background: rgba(99, 102, 241, 0.04); margin: 0;">
                    <input type="radio" name="exportFormat" value="excel" checked style="accent-color: #6366f1; width: 18px; height: 18px; margin: 0 !important; display: inline-block !important; flex-shrink: 0 !important;">
                    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
                        <span class="material-symbols-outlined" style="color: #10b981; font-size: 24px; flex-shrink: 0;">table_chart</span>
                        <div>
                            <div style="font-weight: 700; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 6px;">Excel Spreadsheet (.csv)</div>
                            <div style="font-size: 12px; color: #64748b;">Compatible with MS Excel & Google Sheets</div>
                        </div>
                    </div>
                </label>

                <label class="export-option-card" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 12px; padding: 12px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; cursor: pointer; background: #ffffff; margin: 0;">
                    <input type="radio" name="exportFormat" value="pdf" style="accent-color: #6366f1; width: 18px; height: 18px; margin: 0 !important; display: inline-block !important; flex-shrink: 0 !important;">
                    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
                        <span class="material-symbols-outlined" style="color: #ef4444; font-size: 24px; flex-shrink: 0;">picture_as_pdf</span>
                        <div>
                            <div style="font-weight: 700; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 6px;">PDF Document (.pdf)</div>
                            <div style="font-size: 12px; color: #64748b;">Formatted printable document report</div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
            <button type="button" id="cancelExportBtn" class="btn secondary" style="background: #f1f5f9; color: #475569; font-weight: 600; padding: 9px 16px; border-radius: 8px; border: 1px solid #cbd5e1;">Cancel</button>
            <button type="button" id="confirmExportBtn" class="btn primary" style="background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%); color: #ffffff; font-weight: 700; padding: 9px 20px; border-radius: 8px; border: none; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
                <span class="material-symbols-outlined" style="font-size: 18px;">download</span>
                Download
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('loomEntryModal');
    const addBtn = document.getElementById('addEntryBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    const form = document.getElementById('loomEntryForm');
    const modalTitle = document.getElementById('modalTitle');

    const entryId = document.getElementById('entryId');
    const workerId = document.getElementById('workerId');
    const entryDate = document.getElementById('entryDate');
    const entryType = document.getElementById('entryType');
    const entryQuantity = document.getElementById('entryQuantity');
    const entryDetails = document.getElementById('entryDetails');
    const entryNotes = document.getElementById('entryNotes');

    const openModal = (editData = null) => {
        if (editData) {
            modalTitle.textContent = 'Edit Loom Register Entry';
            entryId.value = editData.id;
            workerId.value = editData.worker_id || '';
            entryDate.value = editData.date;
            entryType.value = editData.type;
            entryQuantity.value = editData.qty;
            entryDetails.value = editData.details || '';
            entryNotes.value = editData.notes || '';
        } else {
            modalTitle.textContent = 'Add Loom Register Entry';
            form.reset();
            entryId.value = '';
            workerId.value = '';
            entryDate.value = new Date().toISOString().split('T')[0];
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

    document.querySelectorAll('.edit-entry-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal({
                id: btn.dataset.id,
                worker_id: btn.dataset.workerId,
                date: btn.dataset.date,
                type: btn.dataset.type,
                qty: btn.dataset.qty,
                details: btn.dataset.details,
                notes: btn.dataset.notes,
            });
        });
    });

    document.querySelectorAll('.delete-entry-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to delete this loom entry?')) return;
            const id = btn.dataset.id;
            try {
                const response = await fetch(`${window.APP_URL}/api/looms/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const res = await response.json();
                if (res.success) {
                    showToast('Loom entry deleted successfully!', 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(res.message || 'Error deleting record.', 'error');
                }
            } catch (err) {
                showToast('Network error while deleting entry.', 'error');
            }
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = entryId.value;
        const payload = {
            worker_id: workerId.value || null,
            entry_date: entryDate.value,
            type: entryType.value,
            quantity: parseFloat(entryQuantity.value),
            details: entryDetails.value,
            notes: entryNotes.value,
        };

        const url = id ? `${window.APP_URL}/api/looms/${id}` : `${window.APP_URL}/api/looms`;
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
                showToast(res.message || 'Loom entry saved successfully!', 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(res.message || 'Error saving loom entry.', 'error');
            }
        } catch (err) {
            showToast('Network error saving loom entry.', 'error');
        }
    });

    // Export Modal Listener & Report Generator
    const exportModal = document.getElementById('exportModal');
    const openExportBtn = document.getElementById('openExportBtn');
    const closeExportBtn = document.getElementById('closeExportModalBtn');
    const cancelExportBtn = document.getElementById('cancelExportBtn');
    const confirmExportBtn = document.getElementById('confirmExportBtn');

    if (openExportBtn) openExportBtn.addEventListener('click', () => exportModal.classList.remove('hidden'));
    if (closeExportBtn) closeExportBtn.addEventListener('click', () => exportModal.classList.add('hidden'));
    if (cancelExportBtn) cancelExportBtn.addEventListener('click', () => exportModal.classList.add('hidden'));

    if (exportModal) {
        exportModal.addEventListener('click', (e) => {
            if (e.target === exportModal) exportModal.classList.add('hidden');
        });

        // Radio button Selection Styling Highlight
        const exportRadios = exportModal.querySelectorAll('input[name="exportFormat"]');
        exportRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                exportRadios.forEach(r => {
                    const card = r.closest('label');
                    if (card) {
                        if (r.checked) {
                            card.style.borderColor = '#6366f1';
                            card.style.background = 'rgba(99, 102, 241, 0.04)';
                        } else {
                            card.style.borderColor = '#cbd5e1';
                            card.style.background = '#ffffff';
                        }
                    }
                });
            });
        });
    }

    if (confirmExportBtn) {
        confirmExportBtn.addEventListener('click', async () => {
            const format = document.querySelector('input[name="exportFormat"]:checked')?.value || 'excel';
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('export', '1');

            try {
                confirmExportBtn.disabled = true;
                confirmExportBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px;">progress_activity</span> Exporting...';

                const response = await fetch(`${window.location.pathname}?${urlParams.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (!data.success || !data.entries) {
                    showToast('Failed to fetch data for export.', 'error');
                    return;
                }

                exportModal.classList.add('hidden');
                const timestamp = typeof getFormattedTimestamp === 'function' ? getFormattedTimestamp() : new Date().toISOString().slice(0, 10);

                if (format === 'excel') {
                    const headers = ['Date', 'Worker Name', 'Warp / Production Details', 'Given to Loom (m)', 'Cloth Received from Worker (m)', 'Remaining Warp Balance (m)', 'Notes / Remarks'];
                    const rows = data.entries.map(e => [
                        e.date,
                        e.worker_name,
                        e.details,
                        e.qty_in,
                        e.qty_out,
                        e.calc_balance,
                        e.notes
                    ]);
                    const totalsRow = ['ALL-TIME OVERALL TOTALS', '', '', `${data.totals.total_given} m`, `${data.totals.total_received} m`, `${data.totals.balance} m`, ''];
                    
                    downloadCSV(`Heavy_Looms_${timestamp}.csv`, headers, rows, totalsRow);
                    showToast('Excel report downloaded successfully!', 'success');
                } else {
                    openPDFReport(
                        'Heavy Looms Production Ledger Report',
                        ['Date', 'Worker Name', 'Production Details', 'Given to Loom', 'Cloth Received', 'Remaining Warp Balance', 'Notes'],
                        data.entries.map(e => [e.date, e.worker_name, e.details, e.qty_in !== '-' ? e.qty_in + ' m' : '-', e.qty_out !== '-' ? e.qty_out + ' m' : '-', e.calc_balance + ' m', e.notes]),
                        [
                            { label: 'Total Given to Loom', val: `${data.totals.total_given} m` },
                            { label: 'Total Cloth Received', val: `${data.totals.total_received} m` },
                            { label: 'Remaining Warp Balance', val: `${data.totals.balance} m` }
                        ],
                        `Heavy_Looms_${timestamp}`
                    );
                    showToast('PDF print preview opened!', 'success');
                }
            } catch (err) {
                showToast('Error generating export report.', 'error');
            } finally {
                confirmExportBtn.disabled = false;
                confirmExportBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px;">download</span> Download';
            }
        });
    }
});
</script>
@endpush
