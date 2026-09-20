@extends('layouts.app')

@section('title', 'Dashboard - GST Billing Application')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
        <h2 class="card-title" style="margin: 0;" data-i18n="dashboard.title">Dashboard Overview</h2>

        <!-- Time Period Tabs (Today, This Month, Last Month, All Time) -->
        <div class="period-tabs-container">
            <button type="button" class="period-tab-btn active" data-period="today" onclick="selectPeriod('today', this)">Today</button>
            <button type="button" class="period-tab-btn" data-period="this_month" onclick="selectPeriod('this_month', this)">This Month</button>
            <button type="button" class="period-tab-btn" data-period="last_month" onclick="selectPeriod('last_month', this)">Last Month</button>
            <button type="button" class="period-tab-btn" data-period="all" onclick="selectPeriod('all', this)">All Time</button>
        </div>
    </div>

    <!-- Summary Metrics -->
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
            <span class="metric-label">Overall Value</span>
            <strong id="dashInvoiceValue">₹0.00</strong>
        </div>
        <div class="metric-card success">
            <span class="metric-label">Paid Received</span>
            <strong id="dashPaidValue" style="color: #10b981;">₹0.00</strong>
        </div>
        <div class="metric-card warning">
            <span class="metric-label" data-i18n="dashboard.outstandingAmount">Outstanding Amount</span>
            <strong id="dashOutstandingValue" style="color: #ef4444;">₹0.00</strong>
        </div>
    </div>

    <!-- Pie & Donut Charts Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 28px;">
        <!-- Chart 1: Revenue Breakdown -->
        <div style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-md); padding: 22px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.05rem; margin: 0; font-family: 'Outfit', sans-serif;">Revenue & Payment Pie Chart</h3>
                <span id="revenueChartSubtitle" style="font-size: 0.8rem; font-weight: 700; color: #6366f1; background: rgba(99,102,241,0.1); padding: 4px 10px; border-radius: 12px;">Today</span>
            </div>
            <div style="position: relative; height: 260px; display: flex; justify-content: center; align-items: center;">
                <canvas id="revenuePieChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Invoice Status Distribution -->
        <div style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-md); padding: 22px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.05rem; margin: 0; font-family: 'Outfit', sans-serif;">Invoice Status Distribution</h3>
                <span id="statusChartSubtitle" style="font-size: 0.8rem; font-weight: 700; color: #6366f1; background: rgba(99,102,241,0.1); padding: 4px 10px; border-radius: 12px;">Today</span>
            </div>
            <div style="position: relative; height: 260px; display: flex; justify-content: center; align-items: center;">
                <canvas id="statusDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <input id="dashSearchInput" type="text" placeholder="Search invoice, customer or amount" class="filter-search-input">
        <div class="filter-date-item">
            <span>From:</span>
            <input id="dashDateFrom" type="date">
        </div>
        <div class="filter-date-item">
            <span>To:</span>
            <input id="dashDateTo" type="date">
        </div>
        <button id="dashResetFilterBtn" class="btn secondary filter-reset-btn" type="button" data-i18n="common.reset">Reset</button>
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
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Loading invoices...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentPeriod = 'today';
let revenueChart = null;
let statusChart = null;

function initCharts() {
    const revenueCtx = document.getElementById('revenuePieChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'pie',
        data: {
            labels: ['Paid Received (₹)', 'Outstanding Amount (₹)'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['#10b981', '#ef4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', weight: '600' } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.label}: ₹${parseFloat(context.raw).toFixed(2)}`;
                        }
                    }
                }
            }
        }
    });

    const statusCtx = document.getElementById('statusDonutChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid Invoices', 'Partial Invoices', 'Unpaid Invoices'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', weight: '600' } } }
            }
        }
    });
}

function selectPeriod(period, btnEl) {
    currentPeriod = period;
    document.querySelectorAll('.period-tab-btn').forEach(btn => btn.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    // Update subtitles
    const labels = { 'today': 'Today', 'this_month': 'This Month', 'last_month': 'Last Month', 'all': 'All Time' };
    const labelText = labels[period] || 'Filtered';
    document.getElementById('revenueChartSubtitle').textContent = labelText;
    document.getElementById('statusChartSubtitle').textContent = labelText;

    loadDashboardMetrics();
}

async function loadDashboardMetrics() {
    const search = document.getElementById('dashSearchInput').value;
    const fromDate = document.getElementById('dashDateFrom').value;
    const toDate = document.getElementById('dashDateTo').value;

    const params = new URLSearchParams();
    params.append('period', currentPeriod);
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
            document.getElementById('dashPaidValue').textContent = '₹' + data.metrics.paidValue;
            document.getElementById('dashOutstandingValue').textContent = '₹' + data.metrics.outstandingValue;

            // Update Charts
            if (revenueChart) {
                const paidVal = parseFloat(data.metrics.paidValue) || 0;
                const outVal = parseFloat(data.metrics.outstandingValue) || 0;
                revenueChart.data.datasets[0].data = (paidVal === 0 && outVal === 0) ? [1, 0] : [paidVal, outVal];
                revenueChart.update();
            }

            if (statusChart) {
                const paidCnt = data.metrics.paidCount || 0;
                const partCnt = data.metrics.partialCount || 0;
                const unpCnt = data.metrics.unpaidCount || 0;
                statusChart.data.datasets[0].data = (paidCnt === 0 && partCnt === 0 && unpCnt === 0) ? [1, 0, 0] : [paidCnt, partCnt, unpCnt];
                statusChart.update();
            }

            // Render Recent Invoices
            const tbody = document.getElementById('recentInvoicesBody');
            tbody.innerHTML = '';

            if (data.recentInvoices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No invoices found for this period.</td></tr>';
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
                        <a href="${window.APP_URL}/invoices/${inv.id}/print" target="_blank" class="btn secondary" style="padding: 4px 10px; font-size: 0.8rem;">Print</a>
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
    initCharts();
    loadDashboardMetrics();
    document.getElementById('dashSearchInput').addEventListener('input', loadDashboardMetrics);
    document.getElementById('dashDateFrom').addEventListener('change', loadDashboardMetrics);
    document.getElementById('dashDateTo').addEventListener('change', loadDashboardMetrics);
    document.getElementById('dashResetFilterBtn').addEventListener('click', () => {
        document.getElementById('dashSearchInput').value = '';
        document.getElementById('dashDateFrom').value = '';
        document.getElementById('dashDateTo').value = '';
        selectPeriod('today', document.querySelector('.period-tab-btn[data-period="today"]'));
    });
});
</script>
@endpush
