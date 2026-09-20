@extends('layouts.app')

@section('title', 'Products - GST Billing Application')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="card-title" style="margin: 0;" data-i18n="nav.products">Products & Inventory</h2>
        <button onclick="showProductModal()" class="btn primary">+ Add Product</button>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('products.index') }}" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Product Name, HSN..." class="filter-search-input">
        <button class="btn secondary filter-reset-btn" type="submit" data-i18n="common.search">Search</button>
        <a href="{{ route('products.index') }}" class="btn secondary filter-reset-btn" style="display: inline-flex; align-items: center;" data-i18n="common.reset">Reset</a>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>HSN / SAC</th>
                    <th>Unit</th>
                    <th>Price (₹)</th>
                    <th>GST Rate (%)</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ $p->hsn_code ?? '-' }}</td>
                    <td><span class="badge secondary">{{ $p->unit }}</span></td>
                    <td>₹{{ number_format($p->price, 2) }}</td>
                    <td>{{ $p->tax_rate }}%</td>
                    <td>
                        <strong style="color: {{ $p->stock <= 5 ? 'var(--warning)' : 'var(--text)' }};">
                            {{ $p->stock }} {{ $p->unit }}
                        </strong>
                    </td>
                    <td style="display: flex; gap: 8px;">
                        <button onclick='editProduct(@json($p))' class="btn secondary" style="padding: 4px 10px; font-size: 0.8rem;">Edit</button>
                        <button onclick="deleteProduct({{ $p->id }})" class="btn danger" style="padding: 4px 10px; font-size: 0.8rem;">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="modal-backdrop hidden">
    <div class="modal-card">
        <h3 id="prodModalTitle" style="margin-top: 0;">Add Product</h3>
        <form id="productForm" class="stack">
            <input type="hidden" id="prodEditId">
            <label>
                <span>Product Name *</span>
                <input id="prodName" type="text" required placeholder="Item Description">
            </label>
            <div class="grid two">
                <label>
                    <span>HSN / SAC Code</span>
                    <input id="prodHsn" type="text" placeholder="e.g. 5208">
                </label>
                <label>
                    <span>Unit</span>
                    <select id="prodUnit">
                        <option value="Kgs">Kgs</option>
                        <option value="Meter">Meter</option>
                        <option value="Pices">Pices</option>
                        <option value="Nos">Nos</option>
                    </select>
                </label>
            </div>
            <div class="grid three">
                <label>
                    <span>Price (₹) *</span>
                    <input id="prodPrice" type="number" step="0.01" required min="0">
                </label>
                <label>
                    <span>GST Rate (%) *</span>
                    <input id="prodTaxRate" type="number" step="0.01" required min="0" value="12">
                </label>
                <label>
                    <span>Stock Quantity *</span>
                    <input id="prodStock" type="number" step="0.01" required value="100">
                </label>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button onclick="hideModal('productModal')" class="btn secondary" type="button">Cancel</button>
                <button class="btn primary" type="submit">Save Product</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showProductModal() {
    document.getElementById('prodModalTitle').textContent = 'Add Product';
    document.getElementById('prodEditId').value = '';
    document.getElementById('productForm').reset();
    showModal('productModal');
}

function editProduct(p) {
    document.getElementById('prodModalTitle').textContent = 'Edit Product';
    document.getElementById('prodEditId').value = p.id;
    document.getElementById('prodName').value = p.name;
    document.getElementById('prodHsn').value = p.hsn_code || '';
    document.getElementById('prodUnit').value = p.unit || 'Kgs';
    document.getElementById('prodPrice').value = p.price;
    document.getElementById('prodTaxRate').value = p.tax_rate;
    document.getElementById('prodStock').value = p.stock;
    showModal('productModal');
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('productForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('prodEditId').value;
        const payload = {
            name: document.getElementById('prodName').value,
            hsn_code: document.getElementById('prodHsn').value,
            unit: document.getElementById('prodUnit').value,
            price: document.getElementById('prodPrice').value,
            tax_rate: document.getElementById('prodTaxRate').value,
            stock: document.getElementById('prodStock').value,
        };

        try {
            const url = id ? `/api/products/${id}` : '/api/products';
            const method = id ? 'PUT' : 'POST';
            const res = await apiFetch(url, { method, body: JSON.stringify(payload) });
            if (res.success) window.location.reload();
        } catch (err) {
            alert('Failed to save product: ' + err.message);
        }
    });
});

async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    try {
        const res = await apiFetch(`/api/products/${id}`, { method: 'DELETE' });
        if (res.success) window.location.reload();
    } catch (e) {
        alert('Failed to delete product: ' + e.message);
    }
}
</script>
@endpush
