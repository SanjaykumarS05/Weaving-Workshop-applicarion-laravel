<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GST Billing Application')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        window.APP_URL = "{{ url('/') }}";
        if (localStorage.getItem('gst_sidebar_collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-init');
        }
    </script>
</head>
<body>
    <div class="app-shell">
        <!-- Logout Confirm Modal -->
        <div id="logoutConfirmModal" class="modal-backdrop hidden">
            <div class="modal-card">
                <h3 id="logoutConfirmTitle" data-i18n="logout.title">Logout session?</h3>
                <p style="color: var(--text-muted);" data-i18n="logout.body">Are you sure you want to logout this session?</p>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                    <button id="confirmLogoutNo" class="btn secondary" type="button" data-i18n="common.cancel">Cancel</button>
                    <button id="confirmLogoutYes" class="btn danger" type="button" data-i18n="nav.logout">Logout</button>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <aside id="mainSidebar" class="sidebar">
            <button id="sidebarToggle" type="button" class="sidebar-toggle-btn" title="Toggle Sidebar">
                <span class="material-symbols-outlined" style="font-size: 20px;">chevron_left</span>
            </button>

            <div class="sidebar-brand">
                <div class="sidebar-mark">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
                <div class="sidebar-brand-text">
                    <strong data-i18n="nav.brand">GST Billing</strong>
                    <span>{{ Auth::user()->business_name ?? 'Workspace' }}</span>
                </div>
            </div>

            <nav class="page-tabs">
                <a href="{{ route('dashboard') }}" class="tab-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="nav-label" data-i18n="nav.dashboard">Dashboard</span>
                </a>
                <a href="{{ route('billing') }}" class="tab-btn {{ request()->routeIs('billing') ? 'active' : '' }}" title="Billing">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <span class="nav-label" data-i18n="nav.billing">Billing</span>
                </a>
                <a href="{{ route('invoices.index') }}" class="tab-btn {{ request()->routeIs('invoices.*') ? 'active' : '' }}" title="Invoices">
                    <span class="material-symbols-outlined">description</span>
                    <span class="nav-label" data-i18n="nav.invoices">Invoices</span>
                </a>
                <a href="{{ route('delivery-sheets.index') }}" class="tab-btn {{ request()->routeIs('delivery-sheets.*') ? 'active' : '' }}" title="Delivery Sheet">
                    <span class="material-symbols-outlined">local_shipping</span>
                    <span class="nav-label" data-i18n="nav.deliverySheet">Delivery Sheet</span>
                </a>
                <a href="{{ route('payments.index') }}" class="tab-btn {{ request()->routeIs('payments.*') ? 'active' : '' }}" title="Payments">
                    <span class="material-symbols-outlined">payments</span>
                    <span class="nav-label" data-i18n="nav.payments">Payments</span>
                </a>
                <a href="{{ route('customers.index') }}" class="tab-btn {{ request()->routeIs('customers.*') ? 'active' : '' }}" title="Customers">
                    <span class="material-symbols-outlined">groups</span>
                    <span class="nav-label" data-i18n="nav.customers">Customers</span>
                </a>
                <a href="{{ route('products.index') }}" class="tab-btn {{ request()->routeIs('products.*') ? 'active' : '' }}" title="Products">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span class="nav-label" data-i18n="nav.products">Products</span>
                </a>
                <a href="{{ route('product-sales.index') }}" class="tab-btn {{ request()->routeIs('product-sales.*') ? 'active' : '' }}" title="Product Sales">
                    <span class="material-symbols-outlined">monitoring</span>
                    <span class="nav-label" data-i18n="nav.productSales">Product Sales</span>
                </a>
                <a href="{{ route('stock-register.index') }}" class="tab-btn {{ request()->routeIs('stock-register.*') ? 'active' : '' }}" title="Stock Register">
                    <span class="material-symbols-outlined">swap_vert</span>
                    <span class="nav-label">Stock Register</span>
                </a>
                <a href="{{ route('looms.index') }}" class="tab-btn {{ request()->routeIs('looms.*') ? 'active' : '' }}" title="Looms">
                    <span class="material-symbols-outlined">precision_manufacturing</span>
                    <span class="nav-label">Looms</span>
                </a>
                <a href="{{ route('workers.index') }}" class="tab-btn {{ request()->routeIs('workers.*') ? 'active' : '' }}" title="Worker">
                    <span class="material-symbols-outlined">engineering</span>
                    <span class="nav-label" data-i18n="nav.workers">Worker</span>
                </a>
                <a href="{{ route('borrows.index') }}" class="tab-btn {{ request()->routeIs('borrows.*') ? 'active' : '' }}" title="Borrow">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                    <span class="nav-label" data-i18n="nav.borrows">Borrow</span>
                </a>
                <a href="{{ route('settings.index') }}" class="tab-btn {{ request()->routeIs('settings.*') ? 'active' : '' }}" title="Settings">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="nav-label" data-i18n="nav.settings">Settings</span>
                </a>
            </nav>

            <button id="logoutBtn" class="tab-btn logout-btn" type="button" title="Logout">
                <span class="material-symbols-outlined">logout</span>
                <span class="nav-label" data-i18n="nav.logout">Logout</span>
            </button>
        </aside>

        <!-- Main Content (Top Bar Language Selector Removed As Requested) -->
        <div id="mainContentShell" class="content-shell">
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/i18n.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('mainSidebar');
            const contentShell = document.getElementById('mainContentShell');
            const toggleBtn = document.getElementById('sidebarToggle');

            const isCollapsed = localStorage.getItem('gst_sidebar_collapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                contentShell.classList.add('collapsed');
                document.documentElement.classList.add('sidebar-collapsed-init');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const collapsed = sidebar.classList.toggle('collapsed');
                    contentShell.classList.toggle('collapsed', collapsed);
                    document.documentElement.classList.toggle('sidebar-collapsed-init', collapsed);
                    localStorage.setItem('gst_sidebar_collapsed', collapsed ? 'true' : 'false');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
