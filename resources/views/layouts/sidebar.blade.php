@php
$isAdmin = auth()->check() && auth()->user()->role === 1;
$isManager = auth()->check() && auth()->user()->role === 2;
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
          <a href="{{ url('/dashboard') }}" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        @if($isManager || $isAdmin)

          {{-- Billing Types --}}
          <li class="nav-item">
            <a href="{{ route('billing-types.index') }}" class="nav-link">
              <i class="nav-icon fas fa-tags"></i>
              <p>Billing Types</p>
            </a>
          </li>

          {{-- Property Category --}}
          <li class="nav-item">
            <a href="{{ route('property-category.index') }}" class="nav-link">
              <i class="nav-icon fas fa-tags"></i>
              <p>Property Category</p>
            </a>
          </li>

          {{-- Properties --}}
          <li class="nav-item">
            <a href="{{ route('property.index') }}" class="nav-link">
              <i class="nav-icon fas fa-building"></i>
              <p>Properties</p>
            </a>
          </li>

          {{-- Units --}} 
          <li class="nav-item">
            <a href="{{ route('unit.index') }}" class="nav-link">
              <i class="nav-icon fas fa-th-large"></i>
              <p>Units</p>
            </a>
          </li>

          {{-- Tenants --}}
          <li class="nav-item">
            <a href="{{ route('tenants.index') }}" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Tenants</p>
            </a>
          </li>

          {{-- Contracts --}}
          <li class="nav-item">
            <a href="{{ route('unit-contracts.index') }}" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Contracts</p> 
            </a>
          </li>
        @endif

        @if($isAdmin)
          {{-- Users --}}
          <li class="nav-item">
            <a href="{{ route('user.index') }}" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Users</p>
            </a>
          </li>
        @endif


        <li class="nav-item">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item" style="color: #a79999;">Logout</button>
          </form>
        </li>
      </ul>
    </nav>
  </div>
</aside>