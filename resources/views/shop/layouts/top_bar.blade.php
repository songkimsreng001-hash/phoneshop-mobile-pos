<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Dashboard') | Mobile Shop POS</title>
    <link rel="shortcut icon" href="{{ asset('admin/assets/media/logos/favicon.ico') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{ asset('admin/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    @yield('header_styles')
    @yield('header_scripts')
</head>
<body id="kt_body" class="bg-body">
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <aside class="sidebar bg-white border-end d-flex flex-column" style="width: 260px; min-height: 100vh;">
                <div class="p-6 border-bottom">
                    <h3 class="mb-0 fw-bolder">Shop Portal</h3>
                </div>
                <div class="p-4">
                    <ul class="nav flex-column gap-2">
                        <li><a class="nav-link px-3 py-2 rounded text-dark" href="{{ route('shop.dashboard') }}"><i class="fas fa-th-large me-2"></i>Dashboard</a></li>
                        <li><a class="nav-link px-3 py-2 rounded text-dark" href="{{ route('shop.pos.show') }}"><i class="fas fa-cash-register me-2"></i>POS</a></li>
                        <li><a class="nav-link px-3 py-2 rounded text-dark" href="{{ route('shop.invoices.index') }}"><i class="fas fa-file-invoice me-2"></i>Invoices</a></li>
                        <li><a class="nav-link px-3 py-2 rounded text-dark" href="{{ route('shop.payments.index') }}"><i class="fas fa-credit-card me-2"></i>Payments</a></li>
                        <li><a class="nav-link px-3 py-2 rounded text-dark" href="{{ route('shop.inventory.show') }}"><i class="fas fa-boxes me-2"></i>Inventory</a></li>
                        <li><a class="nav-link px-3 py-2 rounded text-dark" href="{{ route('shop.customers.index') }}"><i class="fas fa-users me-2"></i>Customers</a></li>
                        <li><a class="nav-link px-3 py-2 rounded text-dark" href="{{ route('shop.claims.show') }}"><i class="fas fa-shield-alt me-2"></i>Claims</a></li>
                    </ul>
                </div>
            </aside>

            <div class="wrapper d-flex flex-column flex-row-fluid">
                <header class="navbar navbar-expand bg-white border-bottom px-6 py-4">
                    <span class="fw-bolder fs-4">@yield('page_title', 'Dashboard')</span>
                    <div class="ms-auto text-muted">{{ optional(auth('web')->user())->name ?? 'Shop User' }}</div>
                </header>

                <main class="content flex-column-fluid p-6">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @yield('footer_modals')
    <script src="{{ asset('admin/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('admin/assets/js/scripts.bundle.js') }}"></script>
    @yield('footer_scripts')
</body>
</html>
