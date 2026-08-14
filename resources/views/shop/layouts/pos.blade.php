@extends('shop.layouts.main')

@section('page_title', 'Point of Sale')

@section('header_styles')
<link href="{{ asset('admin/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<style>
    .pos-card { border: 0; border-radius: 16px; box-shadow: 0 5px 24px rgba(20, 40, 30, .06); }
    .pos-total { background: #f4fbf6; border-radius: 14px; }
    @media print {
        body { background: #fff !important; }
        .no-print, #kt_header, #kt_footer { display: none !important; }
        #print_invoice { display: block !important; width: 4in; margin: auto; }
        #print_invoice table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        #print_invoice th, #print_invoice td { border: 1px solid #000; padding: 5px; word-wrap: break-word; }
        #print_invoice th:nth-child(1), #print_invoice td:nth-child(1) { width: 40%; text-align: left; }
        #print_invoice th:nth-child(2), #print_invoice td:nth-child(2) { width: 15%; text-align: center; }
        #print_invoice th:nth-child(3), #print_invoice td:nth-child(3),
        #print_invoice th:nth-child(4), #print_invoice td:nth-child(4) { width: 22.5%; text-align: right; }
    }
    @media screen { #print_invoice { display: none; } }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-5 no-print">
        <div><h1 class="fw-bold mb-1">Point of Sale</h1><div class="text-muted">Create an invoice, collect payment and print the receipt.</div></div>
        <a href="{{ route('shop.payments.index') }}" class="btn btn-light-success">Payments</a>
    </div>

    <div class="row g-5 no-print">
        <div class="col-xl-7">
            <div class="card pos-card">
                <div class="card-header border-0 pt-6"><div class="card-title flex-column align-items-start"><h3 class="fw-bold mb-1">Products</h3><span class="text-muted">Only products with available stock are shown.</span></div></div>
                <div class="card-body pt-0">
                    <input type="text" id="searchInput" class="form-control form-control-solid mb-5" placeholder="Search products...">
                    <div class="table-responsive">
                        <table class="table align-middle" id="kt_pos_table">
                            <thead><tr><th>Product</th><th>Price</th><th width="120">Qty</th><th width="90"></th></tr></thead>
                            <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td><div class="fw-bold">{{ $product->name }}</div><div class="text-muted fs-7">Stock: {{ $product->qty }}</div></td>
                                    <td><input type="number" class="form-control product-price" data-id="{{ $product->id }}" value="{{ $product->price }}" min="{{ $product->price }}" step="0.01"></td>
                                    <td><input type="number" class="form-control product-quantity" data-id="{{ $product->id }}" value="1" min="1" max="{{ $product->qty }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-success add-to-invoice" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}">Add</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-8">No products available.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card pos-card h-100">
                <div class="card-header border-0 pt-6"><h3 class="card-title fw-bold">Checkout</h3></div>
                <div class="card-body pt-0">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><label class="form-label">Customer name</label><input type="text" class="form-control" id="customer_name" placeholder="Walk-in customer"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" class="form-control" id="customer_phone" placeholder="Optional"></div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-sm align-middle"><thead><tr><th>Item</th><th>Qty</th><th class="text-end">Total</th><th></th></tr></thead><tbody id="invoice_body"></tbody></table>
                        <div id="empty_cart" class="text-muted text-center py-5">Cart is empty.</div>
                    </div>

                    <div class="pos-total p-5">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong><span id="invoice_total">0.00</span> USD</strong></div>
                        <div class="d-flex justify-content-between align-items-center mb-3"><label for="discount_input" class="mb-0">Discount</label><input type="number" step="0.01" min="0" class="form-control w-150px" id="discount_input" value="0"></div>
                        <div class="d-flex justify-content-between fs-3 fw-bold"><span>Total</span><span><span id="final_total">0.00</span> USD</span></div>
                    </div>

                    <div class="mt-5"><label class="form-label fw-bold">Payment method</label><select class="form-select" id="payment_method"><option value="cash">Cash</option><option value="card">Card</option><option value="transfer">Bank Transfer</option></select></div>
                    <div class="mt-4"><label class="form-label fw-bold">Amount paid</label><input type="number" min="0" step="0.01" class="form-control" id="amount_paid" value="0"><div class="form-text">Cash can exceed the total and show change. A lower amount creates a partial payment.</div></div>
                    <div class="alert alert-light-success mt-4 mb-0 d-flex justify-content-between"><span>Change / remaining</span><strong id="payment_balance">0.00 USD</strong></div>
                    <button type="button" class="btn btn-success w-100 mt-5" id="print_button">Complete sale &amp; print</button>
                </div>
            </div>
        </div>
    </div>

    <div id="print_invoice">
        <div class="text-center">
            <h2>{{ $shop_name }}</h2>
            <p>Invoice ID: <span id="print_invoice_id">N/A</span></p>
            <p>Date: <span id="print_date"></span></p>
            <p>Customer: <span id="print_customer_name">N/A</span></p>
            <p>Phone: <span id="print_customer_phone">N/A</span></p>
            <hr>
        </div>
        <table><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody id="print_invoice_body"></tbody></table>
        <hr>
        <h3 class="text-end">Subtotal: <span id="print_total">0.00</span> USD</h3>
        <h3 class="text-end">Discount: <span id="print_discount">0.00</span> USD</h3>
        <h3 class="text-end">Final Total: <span id="print_invoice_total">0.00</span> USD</h3>
        <h3 class="text-end">Paid: <span id="print_amount_paid">0.00</span> USD</h3>
        <h3 class="text-end">Change: <span id="print_change">0.00</span> USD</h3>
        <p class="text-center">Payment: <span id="print_payment_method">Cash</span></p>
        <p class="text-center">Thank you for shopping with us!</p>
    </div>
</div>
@endsection

@section('footer_scripts')
<script src="{{ asset('admin/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let invoiceItems = [];
    let total = 0, discount = 0, finalTotal = 0;
    const amountPaidInput = document.getElementById('amount_paid');
    const paymentMethod = document.getElementById('payment_method');

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }

    function updatePaymentSummary() {
        const paid = Math.max(parseFloat(amountPaidInput.value) || 0, 0);
        const difference = paid - finalTotal;
        const label = document.getElementById('payment_balance');
        label.textContent = (difference >= 0 ? 'Change: ' : 'Remaining: ') + Math.abs(difference).toFixed(2) + ' USD';
        label.className = difference >= 0 ? 'text-success fw-bold' : 'text-warning fw-bold';
    }

    function updateInvoice() {
        const body = document.getElementById('invoice_body');
        body.innerHTML = '';
        invoiceItems.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `<td><div class="fw-bold">${escapeHtml(item.name)}</div><div class="text-muted fs-8">${item.price.toFixed(2)} USD</div></td><td>${item.quantity}</td><td class="text-end">${(item.quantity * item.price).toFixed(2)}</td><td class="text-end"><button type="button" class="btn btn-sm btn-light-danger remove-from-invoice" data-index="${index}">×</button></td>`;
            body.appendChild(row);
        });
        document.getElementById('empty_cart').style.display = invoiceItems.length ? 'none' : 'block';
        total = invoiceItems.reduce((sum, item) => sum + item.quantity * item.price, 0);
        discount = Math.min(Math.max(parseFloat(document.getElementById('discount_input').value) || 0, 0), total);
        finalTotal = Math.max(total - discount, 0);
        document.getElementById('invoice_total').textContent = total.toFixed(2);
        document.getElementById('final_total').textContent = finalTotal.toFixed(2);
        if (document.activeElement !== amountPaidInput && (parseFloat(amountPaidInput.value) || 0) === 0) amountPaidInput.value = finalTotal.toFixed(2);
        updatePaymentSummary();
    }

    function handleAdd(event) {
        const button = event.target.closest('.add-to-invoice');
        if (!button) return;
        const id = button.dataset.id;
        const basePrice = parseFloat(button.dataset.price);
        const priceInput = document.querySelector(`.product-price[data-id="${id}"]`);
        const quantityInput = document.querySelector(`.product-quantity[data-id="${id}"]`);
        const price = parseFloat(priceInput.value), quantity = parseInt(quantityInput.value, 10), max = parseInt(quantityInput.max, 10);
        if (!Number.isFinite(price) || price < basePrice) return alert('Price cannot be below the product base price.');
        if (!Number.isInteger(quantity) || quantity < 1 || quantity > max) return alert('Please enter a valid quantity within available stock.');
        const existing = invoiceItems.find(item => String(item.id) === String(id));
        const nextQty = (existing ? existing.quantity : 0) + quantity;
        if (nextQty > max) return alert('The cart quantity cannot exceed available stock.');
        if (existing) { existing.quantity = nextQty; existing.price = price; }
        else invoiceItems.push({ id, name: button.dataset.name, price, quantity });
        updateInvoice();
    }

    async function saveInvoice() {
        if (!invoiceItems.length) return alert('Invoice is empty.');
        if (finalTotal <= 0) return alert('Final total must be greater than 0.');
        const paid = Math.max(parseFloat(amountPaidInput.value) || 0, 0);
        const method = paymentMethod.value;
        if (method !== 'cash' && paid > finalTotal) return alert('Card and bank-transfer payments cannot exceed the invoice total.');
        const button = document.getElementById('print_button');
        button.disabled = true;
        try {
            const response = await fetch('{{ url('/api/pos/invoice/store') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({
                    products: invoiceItems, total_bill: total, discount, final_bill: finalTotal,
                    customer_name: document.getElementById('customer_name').value || null,
                    customer_phone: document.getElementById('customer_phone').value || null,
                    payment_method: method, amount_paid: paid,
                }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to complete sale.');
            updatePrintInvoice(data);
            window.print();
            window.location.reload();
        } catch (error) {
            alert(error.message);
            button.disabled = false;
        }
    }

    function updatePrintInvoice(data) {
        document.getElementById('print_invoice_id').textContent = data.invoice_id;
        document.getElementById('print_date').textContent = new Date().toLocaleString('en-US');
        document.getElementById('print_customer_name').textContent = document.getElementById('customer_name').value || 'N/A';
        document.getElementById('print_customer_phone').textContent = document.getElementById('customer_phone').value || 'N/A';
        document.getElementById('print_total').textContent = total.toFixed(2);
        document.getElementById('print_discount').textContent = discount.toFixed(2);
        document.getElementById('print_invoice_total').textContent = finalTotal.toFixed(2);
        document.getElementById('print_amount_paid').textContent = Number(data.amount_paid).toFixed(2);
        document.getElementById('print_change').textContent = Number(data.change_amount).toFixed(2);
        document.getElementById('print_payment_method').textContent = paymentMethod.options[paymentMethod.selectedIndex].text;
        document.getElementById('print_invoice_body').innerHTML = invoiceItems.map(item => `<tr><td>${escapeHtml(item.name)}</td><td>${item.quantity}</td><td>${item.price.toFixed(2)}</td><td>${(item.quantity * item.price).toFixed(2)}</td></tr>`).join('');
    }

    document.getElementById('discount_input').addEventListener('input', updateInvoice);
    amountPaidInput.addEventListener('input', updatePaymentSummary);
    document.getElementById('kt_pos_table').addEventListener('click', handleAdd);
    document.getElementById('invoice_body').addEventListener('click', event => {
        const button = event.target.closest('.remove-from-invoice');
        if (!button) return;
        invoiceItems.splice(parseInt(button.dataset.index, 10), 1);
        updateInvoice();
    });
    document.getElementById('print_button').addEventListener('click', saveInvoice);

    const table = $('#kt_pos_table').DataTable({ pageLength: 10 });
    document.getElementById('searchInput').addEventListener('input', function () { table.search(this.value).draw(); });
    updateInvoice();
});
</script>
@endsection
