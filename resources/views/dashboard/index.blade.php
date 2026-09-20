@extends('layouts.app')

@section('title', 'Dashboard - GST Billing Application')

@section('content')
<div class="card">
    <h2 class="card-title" data-i18n="dashboard.title">Dashboard Overview</h2>
    
    <div class="dashboard-metrics">
        <div class="metric-card">
            <span class="metric-label" data-i18n="dashboard.totalInvoices">Total Invoices</span>
            <strong id="dashInvoiceCount">0</strong>
        </div>
        <div class="metric-card">
            <span class="metric-label" data-i18n="nav.customers">Customers</span>
            <strong id="dashCustomerCount">0</strong>
        </div>
        <div class="metric-card">
            <span class="metric-label" data-i18n="nav.products">Products</span>
            <strong id="dashProductCount">0</strong>
        </div>
        <div class="metric-card">
            <span class="metric-label" data-i18n="dashboard.overallValue">Overall Value</span>
            <strong id="dashInvoiceValue">₹0.00</strong>
        </div>
        <div class="metric-card">
            <span class="metric-label" data-i18n="dashboard.thisMonthValue">This Month Value</span>
            <strong id="dashMonthValue">₹0.00</strong>
        </div>
        <div class="metric-card warning">
            <span class="metric-label" data-i18n="dashboard.outstandingAmount">Outstanding Amount</span>
            <strong id="dashOutstandingValue">₹0.00</strong>
        </div>
    </div>

    <!-- Filter Row -->
    <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <input id="dashSearchInput" type="text" placeholder="Search invoice, customer or amount" style="flex: 1; min-width: 200px;">
        <input id="dashDateFrom" type="date">
        <input id="dashDateTo" type="date">
        <button id="dashResetFilterBtn" class="btn secondary" type="button" data-i18n="common.reset">Reset</button>
    </div>

    <!-- Recent Invoices Table -->
    <h3 style="font-size: 1.1rem; margin-bottom: 12px;" data-i18n="dashboard.recentInvoices">Recent Invoices</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="recentInvoicesBody">
                <tr><td colspan="7" style="text-align: center; color: var(--muted);">Loading invoices...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadDashboardMetrics() {
    const search = document.getElementById('dashSearchInput').value;
    const fromDate = document.getElementById('dashDateFrom').value;
    const toDate = document.getElementById('dashDateTo').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);

    try {
        const data = await apiFetch(`/api/dashboard/metrics?${params.toString()}`);
        if (data.success) {
            document.getElementById('dashInvoiceCount').textContent = data.metrics.totalInvoices;
            document.getElementById('dashCustomerCount').textContent = data.metrics.totalCustomers;
            document.getElementById('dashProductCount').textContent = data.metrics.totalProducts;
            document.getElementById('dashInvoiceValue').textContent = '₹' + data.metrics.overallValue;
            document.getElementById('dashMonthValue').textContent = '₹' + data.metrics.monthValue;
            document.getElementById('dashOutstandingValue').textContent = '₹' + data.metrics.outstandingValue;

            const tbody = document.getElementById('recentInvoicesBody');
            tbody.innerHTML = '';

            if (data.recentInvoices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--muted);">No invoices found.</td></tr>';
                return;
            }

            data.recentInvoices.forEach(inv => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${inv.invoice_number}</strong></td>
                    <td>${new Date(inv.invoice_date).toLocaleDateString()}</td>
                    <td>${inv.customer_name}</td>
                    <td>₹${parseFloat(inv.grand_total).toFixed(2)}</td>
                    <td>₹${parseFloat(inv.paid_amount).toFixed(2)}</td>
                    <td><span class="badge ${inv.status}">${inv.status}</span></td>
                    <td>
                        <a href="${window.APP_URL}/invoices/${inv.id}/print" target="_blank" class="btn secondary" style="padding: 4px 8px; font-size: 0.75rem;">Print</a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (e) {
        console.error('Failed to load dashboard metrics', e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardMetrics();
    document.getElementById('dashSearchInput').addEventListener('input', loadDashboardMetrics);
    document.getElementById('dashDateFrom').addEventListener('change', loadDashboardMetrics);
    document.getElementById('dashDateTo').addEventListener('change', loadDashboardMetrics);
    document.getElementById('dashResetFilterBtn').addEventListener('click', () => {
        document.getElementById('dashSearchInput').value = '';
        document.getElementById('dashDateFrom').value = '';
        document.getElementById('dashDateTo').value = '';
        loadDashboardMetrics();
    });
});
</script>
@endpush
