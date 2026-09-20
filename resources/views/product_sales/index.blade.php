@extends('layouts.app')

@section('title', 'Product Sales Report - GST Billing Application')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;" data-i18n="nav.productSales">Product Sales Report</h2>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('product-sales.index') }}" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Product Name..." class="filter-search-input">
        <div class="filter-date-item">
            <span>From:</span>
            <input type="date" name="from_date" value="{{ request('from_date') }}">
        </div>
        <div class="filter-date-item">
            <span>To:</span>
            <input type="date" name="to_date" value="{{ request('to_date') }}">
        </div>
        <button class="btn secondary filter-reset-btn" type="submit" data-i18n="common.search">Filter</button>
        <a href="{{ route('product-sales.index') }}" class="btn secondary filter-reset-btn" style="display: inline-flex; align-items: center;" data-i18n="common.reset">Reset</a>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Unit</th>
                    <th>GST %</th>
                    <th>Total Qty Sold</th>
                    <th>Taxable Amount (₹)</th>
                    <th>Total GST Tax (₹)</th>
                    <th>Total Sales Value (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td><strong>{{ $s->product_name }}</strong></td>
                    <td><span class="badge secondary">{{ $s->unit }}</span></td>
                    <td>{{ $s->tax_rate }}%</td>
                    <td><strong>{{ number_format($s->total_qty, 2) }} {{ $s->unit }}</strong></td>
                    <td>₹{{ number_format($s->total_taxable, 2) }}</td>
                    <td>₹{{ number_format($s->total_tax, 2) }}</td>
                    <td><strong style="color: var(--brand);">₹{{ number_format($s->total_sales, 2) }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No product sales recorded for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
