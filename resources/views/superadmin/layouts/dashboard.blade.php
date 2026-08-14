@extends('superadmin.layouts.app')
@section('page_title', 'Dashboard')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
        <div>
            <div class="text-muted mb-1">Hi {{ $rec->name ?? 'Super Admin' }},</div>
            <h2 class="fw-bold mb-0">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}!</h2>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <button class="btn btn-white border bg-white"><i class="bi bi-calendar3 me-1"></i> This month <i class="bi bi-chevron-down ms-1"></i></button>
            <button class="btn btn-brand"><i class="bi bi-download me-1"></i> Export</button>
        </div>
    </div>

    {{-- Stat cards: Sales / Products / Orders / Customers --}}
    <div class="row g-4 mb-4">
        @php
            $cards = [
                ['label' => 'Total Sales',     'value' => '$'.number_format($totalSales, 2), 'delta' => $salesDelta,     'icon' => 'bi-cash-coin',      'bg' => '#fee2e2', 'fg' => '#ef4444'],
                ['label' => 'Total Products',  'value' => number_format($totalProducts),      'delta' => $productsDelta,  'icon' => 'bi-box-seam-fill',  'bg' => '#dcfce7', 'fg' => '#22c55e'],
                ['label' => 'Total Orders',    'value' => number_format($totalOrders),        'delta' => $ordersDelta,    'icon' => 'bi-receipt-cutoff', 'bg' => '#dbeafe', 'fg' => '#3b82f6'],
                ['label' => 'Total Customers', 'value' => number_format($totalCustomers),     'delta' => $customersDelta, 'icon' => 'bi-people-fill',    'bg' => '#fef3c7', 'fg' => '#f59e0b'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon mb-3" style="background: {{ $c['bg'] }}; color: {{ $c['fg'] }};">
                        <i class="bi {{ $c['icon'] }}"></i>
                    </div>
                    <div class="text-muted small mb-1">{{ $c['label'] }}</div>
                    <div class="d-flex align-items-end gap-2">
                        <span class="fs-3 fw-bold">{{ $c['value'] }}</span>
                    </div>
                    <div class="small mt-1 {{ $c['delta'] >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="bi {{ $c['delta'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($c['delta']) }}% <span class="text-muted">Since last week</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        {{-- Bar chart --}}
        <div class="col-xl-8">
            <div class="card-soft h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Sales, Orders &amp; Customers</h5>
                    <button class="btn btn-sm btn-light border"><i class="bi bi-calendar3 me-1"></i> Last 12 months <i class="bi bi-chevron-down ms-1"></i></button>
                </div>
                <canvas id="salesBarChart" height="110"></canvas>
            </div>
        </div>

        {{-- Donut: order status --}}
        <div class="col-xl-4">
            <div class="card-soft h-100">
                <h5 class="fw-bold mb-3">Order Status</h5>
                <div class="d-flex justify-content-center">
                    <div style="max-width:220px;">
                        <canvas id="orderStatusDonut"></canvas>
                    </div>
                </div>
                @php $totalStatus = max(1, $statusCompleted + $statusVoided + $statusRefunded); @endphp
                <div class="mt-4">
                    <div class="d-flex justify-content-between small mb-1">
                        <span><span class="badge rounded-circle p-1" style="background:#22c55e;">&nbsp;</span> Completed</span>
                        <span class="text-muted">{{ $statusCompleted }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;"><div class="progress-bar bg-success" style="width: {{ $statusCompleted/$totalStatus*100 }}%"></div></div>

                    <div class="d-flex justify-content-between small mb-1">
                        <span><span class="badge rounded-circle p-1" style="background:#3b82f6;">&nbsp;</span> Voided</span>
                        <span class="text-muted">{{ $statusVoided }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;"><div class="progress-bar bg-primary" style="width: {{ $statusVoided/$totalStatus*100 }}%"></div></div>

                    <div class="d-flex justify-content-between small mb-1">
                        <span><span class="badge rounded-circle p-1" style="background:#ef4444;">&nbsp;</span> Refunded</span>
                        <span class="text-muted">{{ $statusRefunded }}</span>
                    </div>
                    <div class="progress" style="height:6px;"><div class="progress-bar bg-danger" style="width: {{ $statusRefunded/$totalStatus*100 }}%"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Recent orders --}}
        <div class="col-xl-6">
            <div class="card-soft h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0">Recent Orders</h5>
                    <a href="{{ route('superadmin.invoices.index') }}" class="small fw-semibold" style="color:var(--brand-green-dark);">View all</a>
                </div>
                @forelse($recentInvoices as $inv)
                    <div class="list-row">
                        <img src="https://ui-avatars.com/api/?background=22c55e&color=fff&name={{ urlencode($inv->customer_name ?: 'Walk-in') }}" class="avatar-sm">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $inv->customer_name ?: 'Walk-in Customer' }}</div>
                            <div class="text-muted small">{{ $inv->shop->name ?? 'Shop #'.$inv->shop_id }} &middot; {{ $inv->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="fw-semibold">${{ number_format($inv->final_bill, 2) }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No orders yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Top shops --}}
        <div class="col-xl-6">
            <div class="card-soft h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0">Top Shops</h5>
                    <a href="{{ route('superadmin.shops') }}" class="small fw-semibold" style="color:var(--brand-green-dark);">View all</a>
                </div>
                @forelse($topShops as $shop)
                    <div class="list-row">
                        <img src="https://ui-avatars.com/api/?background=16a34a&color=fff&name={{ urlencode($shop->name ?? 'Shop') }}" class="avatar-sm">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $shop->name ?? 'Shop #'.$shop->id }}</div>
                            <div class="text-muted small">{{ $shop->products_count }} products listed</div>
                        </div>
                        <span class="badge badge-soft-green rounded-pill px-3 py-2">{{ $shop->status ? 'Blocked' : 'Active' }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No shops yet.</p>
                @endforelse
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')
<script>
    const barCtx = document.getElementById('salesBarChart');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                { label: 'Sales ($)',    data: @json($chartSales),     backgroundColor: '#3b82f6', borderRadius: 4 },
                { label: 'Orders',       data: @json($chartOrders),    backgroundColor: '#a855f7', borderRadius: 4 },
                { label: 'New Customers',data: @json($chartCustomers), backgroundColor: '#f97316', borderRadius: 4 },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    const donutCtx = document.getElementById('orderStatusDonut');
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Voided', 'Refunded'],
            datasets: [{
                data: [{{ $statusCompleted }}, {{ $statusVoided }}, {{ $statusRefunded }}],
                backgroundColor: ['#22c55e', '#3b82f6', '#ef4444'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            }
        }
    });
</script>
@endsection
