@extends('shop.layouts.main')

@section('page_title', 'Payments')

@section('header_styles')
<style>
    .payment-hero { background: linear-gradient(135deg, #18a84b, #2fbd63); color: #fff; border-radius: 18px; }
    .payment-card { border: 0; border-radius: 16px; box-shadow: 0 5px 24px rgba(20, 40, 30, .06); }
    .payment-status { font-size: .75rem; padding: .35rem .65rem; border-radius: 999px; }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold mb-1">Payments</h1>
            <div class="text-muted">Collect outstanding balances and track payment activity.</div>
        </div>
        <a href="{{ route('shop.pos.show') }}" class="btn btn-success">Open POS</a>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-md-6 col-xl-4"><div class="payment-hero p-6 h-100"><div class="text-white-50 mb-2">Outstanding</div><div class="fs-2x fw-bold">{{ number_format($outstanding, 2) }} USD</div><div class="mt-3">Unpaid and partial invoices</div></div></div>
        <div class="col-md-6 col-xl-4"><div class="card payment-card p-6 h-100"><div class="text-muted mb-2">Paid this month</div><div class="fs-2x fw-bold text-dark">{{ number_format($paidThisMonth, 2) }} USD</div><div class="text-muted mt-3">Payments recorded this month</div></div></div>
        <div class="col-xl-4"><div class="card payment-card p-6 h-100"><div class="text-muted mb-2">Invoices awaiting payment</div><div class="fs-2x fw-bold text-dark">{{ $invoices->count() }}</div><div class="text-muted mt-3">Select an invoice below to collect payment</div></div></div>
    </div>

    <div class="card payment-card">
        <div class="card-header border-0 pt-6"><h3 class="card-title fw-bold">Outstanding invoices</h3></div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle" id="payments_table">
                    <thead><tr><th>Invoice</th><th>Customer</th><th>Final Bill</th><th>Paid</th><th>Balance</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                    @forelse($invoices as $invoice)
                        @php $balance = max((float)$invoice->final_bill - (float)$invoice->amount_paid, 0); @endphp
                        <tr>
                            <td class="fw-bold">#{{ $invoice->id }}</td>
                            <td>{{ $invoice->customer_name ?: 'Walk-in customer' }}</td>
                            <td>{{ number_format($invoice->final_bill, 2) }} USD</td>
                            <td>{{ number_format($invoice->amount_paid, 2) }} USD</td>
                            <td class="fw-bold text-danger">{{ number_format($balance, 2) }} USD</td>
                            <td><span class="badge bg-light-warning text-warning payment-status">{{ ucfirst($invoice->payment_status) }}</span></td>
                            <td class="text-end"><button class="btn btn-sm btn-success collect-payment" data-id="{{ $invoice->id }}" data-balance="{{ number_format($balance, 2, '.', '') }}">Collect payment</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-10">No outstanding invoices.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form id="paymentForm">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Collect Payment <span id="paymentInvoiceLabel"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="payment_invoice_id" name="invoice_id">
                <div class="alert alert-light d-flex justify-content-between"><span>Outstanding balance</span><strong id="paymentBalance">0.00 USD</strong></div>
                <div class="mb-4"><label class="form-label">Amount</label><input type="number" min="0.01" step="0.01" class="form-control" id="paymentAmount" name="amount" required></div>
                <div class="mb-4"><label class="form-label">Payment method</label><select class="form-select" name="payment_method" required><option value="cash">Cash</option><option value="card">Card</option><option value="transfer">Bank Transfer</option></select></div>
                <div class="mb-4"><label class="form-label">Reference (optional)</label><input type="text" class="form-control" name="reference" maxlength="100" placeholder="Receipt / transaction reference"></div>
                <div><label class="form-label">Notes (optional)</label><textarea class="form-control" name="notes" rows="2" maxlength="500"></textarea></div>
                <div id="paymentError" class="alert alert-danger mt-4 d-none"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success" id="submitPayment">Record payment</button></div>
        </form>
    </div></div>
</div>
@endsection

@section('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    const form = document.getElementById('paymentForm');
    const amount = document.getElementById('paymentAmount');
    const error = document.getElementById('paymentError');

    document.querySelectorAll('.collect-payment').forEach(button => {
        button.addEventListener('click', () => {
            const balance = parseFloat(button.dataset.balance || '0');
            form.reset();
            document.getElementById('payment_invoice_id').value = button.dataset.id;
            document.getElementById('paymentInvoiceLabel').textContent = '#' + button.dataset.id;
            document.getElementById('paymentBalance').textContent = balance.toFixed(2) + ' USD';
            amount.value = balance.toFixed(2);
            amount.max = balance.toFixed(2);
            error.classList.add('d-none');
            modal.show();
        });
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        error.classList.add('d-none');
        const submit = document.getElementById('submitPayment');
        submit.disabled = true;
        try {
            const response = await fetch('{{ route('shop.payments.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to record payment.');
            window.location.reload();
        } catch (e) {
            error.textContent = e.message;
            error.classList.remove('d-none');
        } finally {
            submit.disabled = false;
        }
    });
});
</script>
@endsection
