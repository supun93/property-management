@php
$isAdmin = auth()->check() && auth()->user()->role === 1;
$isManager = auth()->check() && auth()->user()->role === 2;

// Detect current active route for submenu highlighting
$managerRoutes = ['billing-types*', 'property-category*', 'property*', 'unit*', 'tenants*', 'unit-contracts*'];
$adminRoutes = ['user*'];

$isManagerActive = collect($managerRoutes)->contains(fn($r) => request()->is($r));
$isAdminActive = collect($adminRoutes)->contains(fn($r) => request()->is($r));
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="/" class="brand-link">
    <span class="brand-text font-weight-light">AdminLTE</span>
  </a>

  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        {{-- Dashboard --}}
        <li class="nav-item">
          <a href="{{ url('') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        {{-- Manager Menu --}}
        @if($isManager || $isAdmin)
        <li class="nav-header">Manager Menu</li>

        {{-- Direct Item: Pending Invoices --}}
        <li class="nav-item">
          <a href="{{ route('invoice.pending.index') }}" class="nav-link">
            <i class="nav-icon fas fa-file-invoice-dollar"></i>
            <p>Pending Invoices</p>
          </a>
        </li>

        {{-- Grouped Items --}}
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-cogs"></i>
            <p>
              Manage Master Data
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview pl-3">
            <li class="nav-item">
              <a href="{{ route('billing-types.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Billing Types</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('property-category.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Property Category</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('property.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Properties</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('unit.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Units</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('tenants.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Tenants</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('unit-contracts.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Contracts</p>
              </a>
            </li>
          </ul>
        </li>
        @endif


        {{-- Admin Menu --}}
        @if($isAdmin)
        <li class="nav-item has-treeview {{ $isAdminActive ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ $isAdminActive ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>
              Admin Menu
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview pl-3">
            <li class="nav-item">
              <a href="{{ route('user.index') }}" class="nav-link {{ request()->is('user*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Users</p>
              </a>
            </li>
          </ul>
        </li>
        @endif

        {{-- Logout --}}
        <li class="nav-item mt-3">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item text-muted">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          </form>
        </li>

      </ul>
    </nav>
  </div>
</aside>