@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-body">

    <h5 class="mb-3">👤 <b>Tenant Dashboard</b></h5>

    {{-- Profile --}}
    <h6>📄 <b>Profile</b></h6>
    <p><strong>Name:</strong> {{ $tenant->name }}</p>
    <p><strong>NIC/Passport:</strong> {{ $tenant->nic_passport }}</p>
    <p><strong>Phone:</strong> {{ $tenant->phone }}</p>
    <p><strong>Email:</strong> {{ $tenant->email }}</p>

    {{-- Contract --}}
    @if($contract)
    <hr>
    <h6>🏢 <b>Current Contract</b></h6>
    <p><strong>Unit:</strong> {{ $contract->unit->name ?? '-' }} ({{ $contract->unit->property->name ?? '-' }})</p>
    <p><strong>Agreement:</strong> {{ $contract->start_date }} to {{ $contract->end_date }}</p>
    <p><strong>Rent:</strong> Rs. {{ number_format($contract->rent_amount, 2) }}</p>
    <p><strong>Next Rent Due:</strong> {{ $contract->next_rent_due ?? '-' }}</p>
    @endif

    {{-- Pending Rent Payments --}}
    @if($pendingRentPayments->count() > 0)
    <hr>
    <h5>🔶 Pending Rent Payments</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Month</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Paid At</th>
          <th>Receipt</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pendingRentPayments as $index => $payment)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F Y') }}</td>
          <td>Rs. {{ number_format($payment->amount, 2) }}</td>
          <td><span class="badge badge-warning">PENDING</span></td>
          <td>-</td>
          <td>-</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

    {{-- Paid Rent Payments --}}
    @if($paidRentPayments->count())
    <hr>
    <h5>✅ Paid Rent History</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Month</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Paid At</th>
          <th>Receipt</th>
        </tr>
      </thead>
      <tbody>
        @foreach($paidRentPayments as $index => $payment)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F Y') }}</td>
          <td>Rs. {{ number_format($payment->amount, 2) }}</td>
          <td><span class="badge badge-success">PAID</span></td>
          <td>{{ $payment->paid_at ?? '-' }}</td>
          <td>-</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

    {{-- Pending Utility Payments --}}
    @if($pendingUtilityPayments->count() > 0)
    <hr>
    <h5>🔶 Pending Utility Payments</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Month</th>
          <th>Type</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Paid At</th>
          <th>Receipt</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pendingUtilityPayments as $index => $payment)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F Y') }}</td>
          <td>{{ $payment->unitBillingType->billingType->name ?? '-' }}</td>
          <td>Rs. {{ number_format($payment->amount, 2) }}</td>
          <td><span class="badge badge-warning">PENDING</span></td>
          <td>-</td>
          <td>-</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

    {{-- Paid Utility Payments --}}
    @if($paidUtilityPayments->count())
    <hr>
    <h5>✅ Paid Utility History</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Month</th>
          <th>Type</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Paid At</th>
          <th>Receipt</th>
        </tr>
      </thead>
      <tbody>
        @foreach($paidUtilityPayments as $index => $payment)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F Y') }}</td>
          <td>{{ $payment->unitBillingType->billingType->name ?? '-' }}</td>
          <td>Rs. {{ number_format($payment->amount, 2) }}</td>
          <td><span class="badge badge-success">PAID</span></td>
          <td>{{ $payment->paid_at ?? '-' }}</td>
          <td>-</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

    {{-- Invoice History --}}
    <hr>
    <h5>📄 Invoice History</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date</th>
          <th>PDF</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $index => $invoice)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $invoice->name }}</td>
          <td>Rs. {{ number_format($invoice->amount, 2) }}</td>
          <td>{{ $invoice->status ? 'Paid' : 'Unpaid' }}</td>
          <td>{{ $invoice->payment_date }}</td>
          <td>
            <a href="{{ route('invoice.download', $invoice->id) }}" class="btn btn-sm btn-info">Download</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="6">No invoices found.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-end">
      {{ $invoices->links() }}
    </div>

  </div>
</div>
@endsection
