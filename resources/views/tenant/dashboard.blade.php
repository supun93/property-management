@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

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
        <p><strong>Agreement:</strong> {{ $contract->agreement_start_date }} to {{ $contract->agreement_end_date }}</p>
        <p><strong>Rent:</strong> Rs. {{ number_format($contract->rent_amount, 2) }}</p>
        <p><strong>Next Rent Due:</strong> {{ $contract->next_rent_due_date ?? '-' }}</p>
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
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $index => $invoice)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $invoice->name }}</td>
                    <td>Rs. {{ number_format($invoice->total_amount, 2) }}</td>
                    <td>{{ $invoice->status ? 'Paid' : 'Unpaid' }}</td>
                    <td>{{ $invoice->payment_date }}</td>
                    <td>
                        <a href="{{ route('invoice.upload-form', $invoice->id) }}" class="btn btn-info btn-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">No invoices found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end">
            {{ $invoices->links() }}
        </div>

        {{-- Combined Payments --}}
        <hr>
        <h5>💰 Payment Summary</h5>
        <ul class="nav nav-tabs" id="combinedTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pending-combined-tab" data-toggle="tab" href="#pending-combined" role="tab">🔶 Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="paid-combined-tab" data-toggle="tab" href="#paid-combined" role="tab">✅ Paid</a>
            </li>
        </ul>

        <div class="tab-content mt-3" id="combinedTabContent">
            {{-- Pending Payments --}}
            <div class="tab-pane fade show active" id="pending-combined" role="tabpanel">
                @if($pendingPayments->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Month</th>
                            <th>Billing Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Paid At</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($pendingPayments as $payment)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ isset($payment->unitBillingType) ? 'Utility' : 'Rent' }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F Y') }}</td>
                            <td>{{ $payment->note ?? '-' }}</td>
                            <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                            <td><span class="badge badge-warning">PENDING</span></td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p>No pending payments.</p>
                @endif
            </div>

            {{-- Paid Payments --}}
            <div class="tab-pane fade" id="paid-combined" role="tabpanel">
                @if($paidPayments->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Month</th>
                            <th>Billing Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Paid At</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($paidPayments as $payment)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ isset($payment->unitBillingType) ? 'Utility' : 'Rent' }}</td>
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
                @else
                <p>No paid payments.</p>
                @endif
            </div>
        </div>

        

    </div>
</div>
@endsection
