<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Dashboard') | Admin</title>
    <link rel="shortcut icon" href="{{ asset('admin/assets/media/logos/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root{
            --brand-green:#22c55e;
            --brand-green-dark:#16a34a;
            --brand-green-light:#eafaf0;
            --sidebar-w:260px;
        }
        body{font-family:'Poppins',sans-serif;background:#f4f7f6;color:#1f2937;}
        a{text-decoration:none;}

        .app-sidebar{
            position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w);
            background:linear-gradient(180deg,var(--brand-green) 0%, var(--brand-green-dark) 100%);
            padding:1.5rem 1rem; display:flex; flex-direction:column; z-index:1030; overflow-y:auto;
        }
        .app-sidebar .brand{display:flex;align-items:center;gap:.6rem;color:#fff;font-weight:700;font-size:1.25rem;padding:.5rem .5rem 1.5rem;}
        .app-sidebar .brand i{font-size:1.5rem;}
        .app-sidebar .nav-link{
            color:rgba(255,255,255,.85); font-weight:500; padding:.7rem 1rem; border-radius:.6rem;
            display:flex; align-items:center; gap:.75rem; margin-bottom:.25rem; font-size:.925rem;
        }
        .app-sidebar .nav-link i{font-size:1.05rem;}
        .app-sidebar .nav-link:hover{background:rgba(255,255,255,.14); color:#fff;}
        .app-sidebar .nav-link.active{background:#fff; color:var(--brand-green-dark); font-weight:600;}
        .app-sidebar .nav-section-title{color:rgba(255,255,255,.55); font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; margin:1rem .75rem .35rem;}

        .app-main{margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column;}
        .app-topbar{
            background:#fff; padding:.85rem 1.5rem; display:flex; align-items:center; gap:1rem;
            border-bottom:1px solid #eef0ef; position:sticky; top:0; z-index:1020;
        }
        .app-topbar .search-box{flex:1; max-width:420px;}
        .app-topbar .search-box .form-control{border-radius:.7rem; background:#f4f7f6; border:1px solid #eef0ef;}
        .app-content{padding:1.75rem;}

        .stat-card{background:#fff;border-radius:1rem;padding:1.25rem 1.4rem;border:1px solid #eef0ef;height:100%;}
        .stat-icon{width:42px;height:42px;border-radius:.7rem;display:flex;align-items:center;justify-content:center;font-size:1.15rem;}
        .card-soft{background:#fff;border-radius:1rem;border:1px solid #eef0ef;padding:1.4rem;}
        .btn-brand{background:var(--brand-green);border-color:var(--brand-green);color:#fff;}
        .btn-brand:hover{background:var(--brand-green-dark);border-color:var(--brand-green-dark);color:#fff;}
        .avatar-sm{width:38px;height:38px;border-radius:50%;object-fit:cover;background:#e5e7eb;}
        .list-row{display:flex;align-items:center;justify-content:between;gap:.75rem;padding:.6rem 0;border-bottom:1px solid #f3f4f6;}
        .list-row:last-child{border-bottom:none;}
        .badge-soft-green{background:var(--brand-green-light);color:var(--brand-green-dark);font-weight:600;}
        table.table thead th{border-bottom:2px solid #eef0ef;color:#6b7280;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;font-weight:600;}
        table.table td{vertical-align:middle;}
        .sidebar-toggle{display:none;}
        @media (max-width: 991.98px){
            .app-sidebar{transform:translateX(-100%); transition:transform .25s ease;}
            .app-sidebar.show{transform:translateX(0);}
            .app-main{margin-left:0;}
            .sidebar-toggle{display:inline-flex;}
        }
    </style>
    @yield('header_styles')
</head>
<body>

    <aside class="app-sidebar" id="appSidebar">
        <div class="brand">
            <i class="bi bi-phone-fill"></i>
            <span>Mobile Shop POS</span>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="{{ route('admin.shops') }}" class="nav-link {{ request()->routeIs('admin.shops') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Staff / Shops
            </a>

            <div class="nav-section-title">Catalog</div>
            @adminCan('view_products')
            <a href="{{ url('/admin-panel/shops') }}" class="nav-link {{ request()->routeIs('admin.products.index') || request()->routeIs('admin.inventory.show') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i> Products
            </a>
            @endadminCan
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
                <i class="bi bi-collection-fill"></i> Categories
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="nav-link {{ request()->routeIs('admin.suppliers.index') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> Brands / Suppliers
            </a>

            <div class="nav-section-title">Sales</div>
            <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i> Customers
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-receipt"></i> Orders / Invoices
            </a>

            <div class="nav-section-title">Insights</div>
            @adminCan('view_reports')
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>
            @endadminCan

            {{-- Settings is intentionally never shown here: Admin cannot change system settings. --}}
        </nav>

        <a href="{{ route('admin.logout') }}" class="nav-link mt-auto">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <button class="btn btn-light sidebar-toggle" id="sidebarToggleBtn"><i class="bi bi-list fs-4"></i></button>

            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 position-absolute" style="z-index:5;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="search" id="globalSearchInput" class="form-control ps-5" placeholder="Search products, customers, orders...">
                </div>
            </div>

            <div class="ms-auto d-flex align-items-center gap-3">
                <button class="btn btn-light rounded-circle position-relative" style="width:40px;height:40px;">
                    <i class="bi bi-bell-fill"></i>
                </button>
                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?background=22c55e&color=fff&name={{ urlencode(auth('admin')->user()->name ?? 'Admin') }}" class="avatar-sm">
                        <span class="fw-semibold">{{ auth('admin')->user()->name ?? 'Admin' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.logout') }}"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="app-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('sidebarToggleBtn')?.addEventListener('click', function () {
            document.getElementById('appSidebar').classList.toggle('show');
        });

        (function () {
            const input = document.getElementById('globalSearchInput');
            if (!input) return;
            const params = new URLSearchParams(window.location.search);
            const q = params.get('q') || localStorage.getItem('lastSearchTerm') || '';
            if (q) input.value = q;
            input.addEventListener('input', function () {
                localStorage.setItem('lastSearchTerm', input.value);
            });
        })();

        // Reusable SweetAlert2 confirm helper for delete forms.
        // Usage: <form class="js-confirm-delete" data-confirm-text="Delete this product?" method="POST" ...>
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form.classList || !form.classList.contains('js-confirm-delete')) return;
            if (form.dataset.confirmed === 'true') return;
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: form.dataset.confirmText || 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });

        @if(session('success'))
            Swal.fire({icon:'success', title: @json(session('success')), timer:2200, showConfirmButton:false});
        @endif
        @if(session('error'))
            Swal.fire({icon:'error', title: @json(session('error'))});
        @endif
    </script>
    @yield('footer_scripts')
</body>
</html>
