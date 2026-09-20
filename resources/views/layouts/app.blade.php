<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GST Billing Application')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        window.APP_URL = "{{ url('/') }}";
    </script>
</head>
<body>
    <div class="app-shell">
        <!-- Logout Confirm Modal -->
        <div id="logoutConfirmModal" class="modal-backdrop hidden">
            <div class="modal-card">
                <h3 id="logoutConfirmTitle" data-i18n="logout.title">Logout session?</h3>
                <p style="color: var(--muted);" data-i18n="logout.body">Are you sure you want to logout this session?</p>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                    <button id="confirmLogoutNo" class="btn secondary" type="button" data-i18n="common.cancel">Cancel</button>
                    <button id="confirmLogoutYes" class="btn danger" type="button" data-i18n="nav.logout">Logout</button>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-mark">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
                <div class="sidebar-brand-text">
                    <strong data-i18n="nav.brand">GST Billing</strong>
                    <span>{{ Auth::user()->business_name ?? 'Workspace' }}</span>
                </div>
            </div>

            @if(Auth::user() && Auth::user()->trial_status['active'] && !Auth::user()->trial_status['unlimited'])
            <div class="trial-badge">
                <span class="material-symbols-outlined">bolt</span>
                <span>{{ Auth::user()->trial_status['daysLeft'] }} Days Trial Left</span>
            </div>
            @endif

            <nav class="page-tabs">
                <a href="{{ route('dashboard') }}" class="tab-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span data-i18n="nav.dashboard">Dashboard</span>
                </a>
                <a href="{{ route('billing') }}" class="tab-btn {{ request()->routeIs('billing') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <span data-i18n="nav.billing">Billing</span>
                </a>
                <a href="{{ route('invoices.index') }}" class="tab-btn {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">description</span>
                    <span data-i18n="nav.invoices">Invoices</span>
                </a>
                <a href="{{ route('delivery-sheets.index') }}" class="tab-btn {{ request()->routeIs('delivery-sheets.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">local_shipping</span>
                    <span data-i18n="nav.deliverySheet">Delivery Sheet</span>
                </a>
                <a href="{{ route('payments.index') }}" class="tab-btn {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">payments</span>
                    <span data-i18n="nav.payments">Payments</span>
                </a>
                <a href="{{ route('customers.index') }}" class="tab-btn {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">groups</span>
                    <span data-i18n="nav.customers">Customers</span>
                </a>
                <a href="{{ route('products.index') }}" class="tab-btn {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span data-i18n="nav.products">Products</span>
                </a>
                <a href="{{ route('product-sales.index') }}" class="tab-btn {{ request()->routeIs('product-sales.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">monitoring</span>
                    <span data-i18n="nav.productSales">Product Sales</span>
                </a>
                <a href="{{ route('settings.index') }}" class="tab-btn {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">settings</span>
                    <span data-i18n="nav.settings">Settings</span>
                </a>
            </nav>

            <button id="logoutBtn" class="tab-btn logout-btn" type="button">
                <span class="material-symbols-outlined">logout</span>
                <span data-i18n="nav.logout">Logout</span>
            </button>
        </aside>

        <!-- Main Content -->
        <div class="content-shell">
            <header class="content-topbar">
                <div class="topbar-language">
                    <span class="material-symbols-outlined">translate</span>
                    <select id="languageSwitcher">
                        <option value="en">English</option>
                        <option value="hi">हिन्दी</option>
                        <option value="ta">தமிழ்</option>
                    </select>
                </div>
            </header>

            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/i18n.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
