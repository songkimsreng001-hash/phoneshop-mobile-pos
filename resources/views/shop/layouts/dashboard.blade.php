@extends('shop.layouts.main')

@section('page_title', 'Dashboard')

@section('header_styles')
<style>
    .dashboard-shell { max-width: 1500px; margin: 0 auto; }
    .welcome-card { background: linear-gradient(135deg, #19a84c 0%, #2dbc62 100%); border-radius: 18px; color: #fff; overflow: hidden; position: relative; }
    .welcome-card:after { content: ''; position: absolute; width: 240px; height: 240px; border-radius: 50%; right: -80px; top: -100px; background: rgba(255,255,255,.10); }
    .stat-card, .panel-card { border: 0; border-radius: 16px; box-shadow: 0 5px 24px rgba(20,40,30,.06); }
    .stat-icon { width: 44px; height: 44px; border-radius: 12px; display:flex; align-items:center; justify-content:center; background:#eef9f1; color:#19a84c; }
    .chart-bar { border-radius: 7px 7px 2px 2px; background: #20ae52; min-height: 6px; }
    .chart-track { height: 160px; display:flex; align-items:flex-end; gap:12px; }
    .chart-column { flex:1; height:100%; display:flex; align-items:flex-end; }
    .chart-label { font-size: .75rem; color:#8a9690; text-align:center; margin-top:8px; }
    .payment-donut { width: 150px; height: 150px; border-radius:50%; background: conic-gradient(#19a84c 0 var(--paid-ratio), #f5b83d var(--paid-ratio) 100%); position:relative; }
    .payment-donut:after { content:''; position:absolute; inset:28px; border-radius:50%; background:#fff; }
    .payment-donut span { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; z-index:1; font-weight:700; font-size:1.3rem; }
    .status-dot { width:9px; height:9px; border-radius:50%; display:inline-block; margin-right:6px; }
    .table > :not(caption) > * > * { padding-top: .9rem; padding-bottom: .9rem; }
</style>
@endsection

@section('content')
<div class="dashboard-shell">
    <div class="welcome-card p-7 mb-6">
        <div class="position-relative" style="z-index:2;">
            <div class="text-white-50 mb-2">{{ now()->format('l, d F Y') }}</div>
            <h1 class="text-white fw-bold mb-2">Good morning, {{ $user->name }}!</h1>
            <div class="text-white opacity-75">Here is your shop's sales, inventory and payment overview.</div>
        </div>
    </div>

    <div class="row g-5 mb-6">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-5 h-100">
                <div class="d-flex justify-content-between align-items-start"><div class="stat-icon"><i class="fas fa-shopping-cart"></i></div><span class="badge bg-light-success text-success">Sales</span></div>
                <div class="fs-2x fw-bold mt-5">{{ number_format($salesCount) }}</div>
                <div class="text-muted">Completed invoices</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-5 h-100">
                <div class="d-flex justify-content-between align-items-start"><div class="stat-icon"><i class="fas fa-box"></i></div><span class="badge bg-light-info text-info">Stock</span></div>
                <div class="fs-2x fw-bold mt-5">{{ number_format($stockUnits) }}</div>
                <div class="text-muted">Units in inventory</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-5 h-100">
                <div class="d-flex justify-content-between align-items-start"><div class="stat-icon"><i class="fas fa-users"></i></div><span class="badge bg-light-primary text-primary">Customers</span></div>
                <div class="fs-2x fw-bold mt-5">{{ number_format($customersCount) }}</div>
                <div class="text-muted">Customers with purchases</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card p-5 h-100">
                <div class="d-flex justify-content-between align-items-start"><div class="stat-icon"><i class="fas fa-coins"></i></div><span class="badge bg-light-warning text-warning">Revenue</span></div>
                <div class="fs-2x fw-bold mt-5">{{ number_format($monthlyRevenue, 2) }} USD</div>
                <div class="text-muted">Revenue this month</div>
            </div>
        </div>
    </div>

    <div class="row g-5 mb-6">
        <div class="col-xl-8">
            <div class="card panel-card h-100">
                <div class="card-header border-0 pt-6">
                    <div><h3 class="card-title fw-bold mb-1">Sales overview</h3><div class="text-muted">Revenue for the last six months</div></div>
                    <a href="{{ route('shop.invoices.index') }}" class="btn btn-sm btn-light-success">View invoices</a>
                </div>
                <div class="card-body pt-3">
                    <div class="chart-track">
                    @foreach($monthlyChart as $month)
                        <div class="chart-column" title="{{ number_format($month['amount'], 2) }} USD">
                            <div class="chart-bar w-100" style="height: {{ max(($month['amount'] / $maxChart) * 100, 4) }}%"></div>
                        </div>
                    @endforeach
                    </div>
                    <div class="d-flex gap-3">
                        @foreach($monthlyChart as $month)<div class="flex-fill chart-label">{{ $month['label'] }}</div>@endforeach
                    </div>
                    <div class="d-flex justify-content-between mt-6 p-4 rounded bg-light-success">
                        <div><div class="text-muted fs-7">Monthly revenue</div><strong class="fs-4">{{ number_format($monthlyRevenue, 2) }} USD</strong></div>
                        <div class="text-end"><div class="text-muted fs-7">Collected this month</div><strong class="fs-4">{{ number_format($monthlyPaid, 2) }} USD</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header border-0 pt-6"><div><h3 class="card-title fw-bold mb-1">Payment status</h3><div class="text-muted">Current collection position</div></div></div>
                <div class="card-body pt-2">
                    @php
                        $paidRatio = $revenue > 0 ? min(max((($revenue - $outstanding) / $revenue) * 100, 0), 100) : 0;
                    @endphp
                    <div class="d-flex justify-content-center mb-5"><div class="payment-donut" style="--paid-ratio: {{ $paidRatio }}%"><span>{{ number_format($paidRatio, 0) }}%</span></div></div>
                    <div class="d-flex justify-content-between mb-3"><span><span class="status-dot" style="background:#19a84c"></span>Paid</span><strong>{{ number_format($monthlyPaid, 2) }} USD</strong></div>
                    <div class="d-flex justify-content-between mb-3"><span><span class="status-dot" style="background:#f5b83d"></span>Outstanding</span><strong>{{ number_format($outstanding, 2) }} USD</strong></div>
                    <div class="d-flex justify-content-between"><span><span class="status-dot" style="background:#22a9df"></span>Products</span><strong>{{ number_format($productsCount) }}</strong></div>
                    <div class="border-top mt-4 pt-4">
                        <div class="text-muted fs-7 mb-3">Collected by method</div>
                        @foreach(['cash' => 'Cash', 'card' => 'Card', 'transfer' => 'Transfer', 'mixed' => 'Mixed'] as $method => $label)
                            @if($paymentMethods->has($method))
                                <div class="d-flex justify-content-between mb-2"><span>{{ $label }}</span><strong>{{ number_format($paymentMethods[$method]->amount, 2) }} USD</strong></div>
                            @endif
                        @endforeach
                    </div>
                    <a href="{{ route('shop.payments.index') }}" class="btn btn-success w-100 mt-5">Manage payments</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 mb-6">
        <div class="col-xl-8">
            <div class="card panel-card">
                <div class="card-header border-0 pt-6"><h3 class="card-title fw-bold">Recent invoices</h3></div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Payment</th><th>Date</th></tr></thead>
                            <tbody>
                            @forelse($recentInvoices as $invoice)
                                <tr>
                                    <td class="fw-bold">#{{ $invoice->id }}</td>
                                    <td>{{ $invoice->customer_name ?: ($invoice->customer?->name ?: 'Walk-in customer') }}</td>
                                    <td>{{ number_format($invoice->final_bill, 2) }} USD</td>
                                    <td><span class="badge {{ $invoice->payment_status === 'paid' ? 'bg-light-success text-success' : 'bg-light-warning text-warning' }}">{{ ucfirst($invoice->payment_status) }}</span></td>
                                    <td class="text-muted">{{ $invoice->created_at?->format('d M, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-8">No invoices yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card panel-card h-100">
                <div class="card-header border-0 pt-6"><h3 class="card-title fw-bold">Low stock</h3><span class="badge bg-light-danger text-danger">{{ $lowStockCount }}</span></div>
                <div class="card-body pt-0">
                @forelse($lowStockProducts as $product)
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div><div class="fw-bold">{{ $product->name }}</div><div class="text-muted fs-7">Reorder at {{ $product->reorder_level }}</div></div>
                        <span class="badge bg-light-danger text-danger">{{ $product->qty }} left</span>
                    </div>
                @empty
                    <div class="text-center text-muted py-8">Stock levels look good.</div>
                @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-4"><div class="card panel-card p-5"><div class="text-muted">Outstanding payments</div><div class="fs-2x fw-bold text-danger mt-2">{{ number_format($outstanding, 2) }} USD</div><a href="{{ route('shop.payments.index') }}" class="btn btn-light-danger mt-4">Collect payments</a></div></div>
        <div class="col-xl-4"><div class="card panel-card p-5"><div class="text-muted">Products</div><div class="fs-2x fw-bold mt-2">{{ number_format($productsCount) }}</div><div class="text-muted mt-2">{{ number_format($lowStockCount) }} need restocking</div></div></div>
        <div class="col-xl-4"><div class="card panel-card p-5"><div class="text-muted">Purchases</div><div class="fs-2x fw-bold mt-2">{{ number_format($purchasesCount) }}</div><div class="text-muted mt-2">Recorded purchase orders</div></div></div>
    </div>
</div>
@endsection
