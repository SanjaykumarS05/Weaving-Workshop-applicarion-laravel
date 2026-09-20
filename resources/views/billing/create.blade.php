@extends('layouts.app')

@section('title', 'Create Invoice - GST Billing Application')

@section('content')
<div class="card">
    <h2 class="card-title" data-i18n="billing.createInvoice">Create Invoice</h2>

    <form id="createInvoiceForm" class="stack">
        <!-- Customer & Date Header -->
        <div class="grid two">
            <label>
                <span data-i18n="billing.customer">Customer Name</span>
                <input id="customerName" type="text" list="customerDatalist" required placeholder="Type customer name">
                <datalist id="customerDatalist"></datalist>
            </label>
            <label>
                <span data-i18n="billing.customerGstin">Customer GSTIN</span>
                <input id="customerGstin" type="text" placeholder="29AAAAA0000A1Z5">
            </label>
        </div>

        <div class="grid two">
            <label>
                <span data-i18n="billing.buyerAddress">Buyer Address</span>
                <textarea id="customerAddress" rows="2" placeholder="Full address"></textarea>
            </label>
            <label>
                <span data-i18n="common.state">State</span>
                <select id="customerState" class="state-select"></select>
            </label>
        </div>

        <div class="grid three">
            <label>
                <span data-i18n="billing.contactNumber">Contact Number</span>
                <input id="customerPhone" type="text">
            </label>
            <label>
                <span data-i18n="billing.buyerEmail">Buyer Email</span>
                <input id="customerEmail" type="email">
            </label>
            <label>
                <span data-i18n="billing.billDate">Bill Date</span>
                <input id="billDate" type="date" required value="{{ date('Y-m-d') }}">
            </label>
        </div>

        <div class="grid two">
            <label>
                <span data-i18n="billing.eWayBillNo">e-Way Bill No</span>
                <input id="eWayBillNo" type="text">
            </label>
            <label>
                <span data-i18n="billing.supplyType">Supply Type</span>
                <select id="supplyType">
                    <option value="intra">Intra-state (CGST + SGST)</option>
                    <option value="inter">Inter-state (IGST)</option>
                    <option value="none">No Tax</option>
                </select>
            </label>
        </div>

        <!-- Items Table -->
        <div style="margin-top: 16px;">
            <h3 style="font-size: 1.05rem; margin-bottom: 12px;">Invoice Items</h3>
            <div class="table-responsive">
                <table id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Product / Description</th>
                            <th>HSN Code</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Rate (₹)</th>
                            <th>GST %</th>
                            <th>Amount (₹)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <!-- Dynamic Item Rows -->
                    </tbody>
                </table>
            </div>
            <button id="addItemRowBtn" class="btn secondary" type="button" style="margin-top: 12px;">+ Add Product Row</button>
        </div>

        <!-- Calculations & Summary Footer -->
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-top: 24px;">
            <div>
                <label>
                    <span>Notes / Terms</span>
                    <textarea id="invoiceNotes" rows="4" placeholder="Terms and conditions or notes..."></textarea>
                </label>
            </div>

            <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border: 1px solid var(--line);" class="stack">
                <div style="display: flex; justify-content: space-between;">
                    <span>Subtotal:</span>
                    <strong id="lblSubtotal">₹0.00</strong>
                </div>
                <div id="cgstRow" style="display: flex; justify-content: space-between;">
                    <span>CGST:</span>
                    <span id="lblCgst">₹0.00</span>
                </div>
                <div id="sgstRow" style="display: flex; justify-content: space-between;">
                    <span>SGST:</span>
                    <span id="lblSgst">₹0.00</span>
                </div>
                <div id="igstRow" style="display: flex; justify-content: space-between; display: none;">
                    <span>IGST:</span>
                    <span id="lblIgst">₹0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Discount (₹):</span>
                    <input id="discountInput" type="number" step="0.01" value="0.00" style="width: 100px; text-align: right;">
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Round Off:</span>
                    <span id="lblRoundOff">₹0.00</span>
                </div>
                <hr style="border: none; border-top: 1px solid var(--line); margin: 4px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 1.15rem; color: var(--brand);">
                    <strong>Grand Total:</strong>
                    <strong id="lblGrandTotal">₹0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                    <span>Paid Amount (₹):</span>
                    <input id="paidAmountInput" type="number" step="0.01" value="0.00" style="width: 110px; text-align: right;">
                </div>
                <label style="margin-top: 4px;">
                    <span>Payment Mode</span>
                    <select id="paymentModeSelect">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / GPay</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </label>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <a href="{{ route('invoices.index') }}" class="btn secondary" data-i18n="common.cancel">Cancel</a>
            <button class="btn primary" type="submit">Save & Print Invoice</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let existingCustomers = [];
let existingProducts = [];

async function loadDataForBilling() {
    try {
        const [cData, pData] = await Promise.all([
            apiFetch('/customers'),
            apiFetch('/products')
        ]);
        existingCustomers = cData.customers || [];
        existingProducts = pData.products || [];

        const datalist = document.getElementById('customerDatalist');
        datalist.innerHTML = '';
        existingCustomers.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.name;
            datalist.appendChild(opt);
        });

        addItemRow();
    } catch (e) {
        console.error(e);
    }
}

function addItemRow(productData = null) {
    const tbody = document.getElementById('itemsTbody');
    const tr = document.createElement('tr');
    tr.className = 'item-row';

    let productOptions = '<option value="">Custom Item</option>';
    existingProducts.forEach(p => {
        productOptions += `<option value="${p.id}">${p.name} (Stock: ${p.stock})</option>`;
    });

    tr.innerHTML = `
        <td>
            <select class="product-select" style="margin-bottom: 4px;">${productOptions}</select>
            <input type="text" class="item-name" placeholder="Item Name" required>
        </td>
        <td><input type="text" class="item-hsn" placeholder="HSN/SAC"></td>
        <td>
            <select class="item-unit">
                <option value="Kgs">Kgs</option>
                <option value="Meter">Meter</option>
                <option value="Pices">Pices</option>
                <option value="Nos">Nos</option>
            </select>
        </td>
        <td><input type="number" class="item-qty" value="1" min="0.01" step="any" required style="width: 70px;"></td>
        <td><input type="number" class="item-rate" value="0.00" min="0" step="any" required style="width: 90px;"></td>
        <td><input type="number" class="item-tax" value="12" min="0" step="any" style="width: 65px;"></td>
        <td><strong class="item-total">₹0.00</strong></td>
        <td><button class="btn danger remove-row-btn" type="button" style="padding: 4px 8px; font-size: 0.75rem;">X</button></td>
    `;

    tbody.appendChild(tr);

    const prodSelect = tr.querySelector('.product-select');
    const nameInput = tr.querySelector('.item-name');
    const hsnInput = tr.querySelector('.item-hsn');
    const unitSelect = tr.querySelector('.item-unit');
    const rateInput = tr.querySelector('.item-rate');
    const taxInput = tr.querySelector('.item-tax');

    prodSelect.addEventListener('change', (e) => {
        const prod = existingProducts.find(p => p.id == e.target.value);
        if (prod) {
            nameInput.value = prod.name;
            hsnInput.value = prod.hsn_code || '';
            unitSelect.value = prod.unit || 'Kgs';
            rateInput.value = prod.price;
            taxInput.value = prod.tax_rate;
            calculateInvoiceTotals();
        }
    });

    tr.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', calculateInvoiceTotals);
        el.addEventListener('change', calculateInvoiceTotals);
    });

    tr.querySelector('.remove-row-btn').addEventListener('click', () => {
        if (document.querySelectorAll('.item-row').length > 1) {
            tr.remove();
            calculateInvoiceTotals();
        }
    });

    calculateInvoiceTotals();
}

function calculateInvoiceTotals() {
    const supplyType = document.getElementById('supplyType').value;
    let subtotal = 0;
    let totalCgst = 0;
    let totalSgst = 0;
    let totalIgst = 0;

    document.querySelectorAll('.item-row').forEach(tr => {
        const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
        const rate = parseFloat(tr.querySelector('.item-rate').value) || 0;
        const taxRate = parseFloat(tr.querySelector('.item-tax').value) || 0;

        const lineTaxable = qty * rate;
        let cgst = 0, sgst = 0, igst = 0;

        if (supplyType === 'intra') {
            const half = taxRate / 2;
            cgst = lineTaxable * (half / 100);
            sgst = lineTaxable * (half / 100);
        } else if (supplyType === 'inter') {
            igst = lineTaxable * (taxRate / 100);
        }

        const lineTotal = lineTaxable + cgst + sgst + igst;
        tr.querySelector('.item-total').textContent = '₹' + lineTotal.toFixed(2);

        subtotal += lineTaxable;
        totalCgst += cgst;
        totalSgst += sgst;
        totalIgst += igst;
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const rawTotal = Math.max(0, (subtotal + totalCgst + totalSgst + totalIgst) - discount);
    const roundedTotal = Math.round(rawTotal);
    const roundOff = roundedTotal - rawTotal;

    document.getElementById('lblSubtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('lblCgst').textContent = '₹' + totalCgst.toFixed(2);
    document.getElementById('lblSgst').textContent = '₹' + totalSgst.toFixed(2);
    document.getElementById('lblIgst').textContent = '₹' + totalIgst.toFixed(2);

    if (supplyType === 'inter') {
        document.getElementById('cgstRow').style.display = 'none';
        document.getElementById('sgstRow').style.display = 'none';
        document.getElementById('igstRow').style.display = 'flex';
    } else {
        document.getElementById('cgstRow').style.display = 'flex';
        document.getElementById('sgstRow').style.display = 'flex';
        document.getElementById('igstRow').style.display = 'none';
    }

    document.getElementById('lblRoundOff').textContent = '₹' + roundOff.toFixed(2);
    document.getElementById('lblGrandTotal').textContent = '₹' + roundedTotal.toFixed(2);
}

document.addEventListener('DOMContentLoaded', () => {
    loadDataForBilling();
    document.getElementById('addItemRowBtn').addEventListener('click', () => addItemRow());
    document.getElementById('supplyType').addEventListener('change', calculateInvoiceTotals);
    document.getElementById('discountInput').addEventListener('input', calculateInvoiceTotals);

    // Customer Name Autofill
    document.getElementById('customerName').addEventListener('input', (e) => {
        const cust = existingCustomers.find(c => c.name.toLowerCase() === e.target.value.trim().toLowerCase());
        if (cust) {
            document.getElementById('customerPhone').value = cust.phone || '';
            document.getElementById('customerEmail').value = cust.email || '';
            document.getElementById('customerGstin').value = cust.gstin || '';
            document.getElementById('customerAddress').value = cust.address || '';
            if (cust.state) document.getElementById('customerState').value = cust.state;
        }
    });

    // Form Submit
    document.getElementById('createInvoiceForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const items = [];
        document.querySelectorAll('.item-row').forEach(tr => {
            items.push({
                product_id: tr.querySelector('.product-select').value || null,
                product_name: tr.querySelector('.item-name').value,
                hsn_code: tr.querySelector('.item-hsn').value,
                unit: tr.querySelector('.item-unit').value,
                quantity: tr.querySelector('.item-qty').value,
                rate: tr.querySelector('.item-rate').value,
                tax_rate: tr.querySelector('.item-tax').value,
            });
        });

        const stateSelect = document.getElementById('customerState');
        const selectedOpt = stateSelect.options[stateSelect.selectedIndex];

        const payload = {
            customer_name: document.getElementById('customerName').value,
            customer_phone: document.getElementById('customerPhone').value,
            customer_email: document.getElementById('customerEmail').value,
            customer_gstin: document.getElementById('customerGstin').value,
            customer_address: document.getElementById('customerAddress').value,
            customer_state: stateSelect.value,
            customer_state_code: selectedOpt ? selectedOpt.getAttribute('data-code') : '',
            supply_type: document.getElementById('supplyType').value,
            invoice_date: document.getElementById('billDate').value,
            e_way_bill_no: document.getElementById('eWayBillNo').value,
            discount_amount: document.getElementById('discountInput').value,
            paid_amount: document.getElementById('paidAmountInput').value,
            payment_mode: document.getElementById('paymentModeSelect').value,
            notes: document.getElementById('invoiceNotes').value,
            items: items,
        };

        try {
            const res = await apiFetch('/api/invoices', {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (res.success) {
                window.open(res.print_url, '_blank');
                window.location.href = window.APP_URL + '/invoices';
            }
        } catch (err) {
            alert('Failed to save invoice: ' + err.message);
        }
    });
});
</script>
@endpush
