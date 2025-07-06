@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />

<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Unit Payment - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('unit-payment-schedules.index', $record->unit_contract_id) }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('unit-payment-schedules.trash-list', $record->unit_contract_id) }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('unit-payment-schedules.update', $record->id) }}">
    @csrf
    <div class="card-body">

      <div class="form-group">
        <label>Payment Date</label>
        <input type="date" name="payment_date" value="{{ $record->payment_date }}" class="form-control" required readonly>
      </div>

      <div class="form-group">
        <label>Amount</label>
        <input type="number" step="0.01" name="amount" value="{{ $record->amount }}" placeholder="0.00" class="form-control" required @if($record->is_rent) readonly @endif>
      </div>

      @if($record->is_rent)
      <div class="form-group">
        <label>Installment Number</label>
        <input type="number" name="installment_number" value="{{ $record->installment_number }}" class="form-control" readonly>
      </div>
      @endif
      <div class="form-group">
        <label>Note</label>
        <input type="text" name="note" value="{{ $record->note }}" class="form-control" readonly>
      </div>
      <div class="form-group">
        <label>Remarks</label>
        <textarea name="approval_remarks" class="form-control" rows="3" placeholder="Remarks">{{ $record->approval_remarks }}</textarea>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="0" {{ $record->status == 0 ? 'selected' : '' }}>Pending</option>
          <option value="1" {{ $record->status == 1 ? 'selected' : '' }}>Paid</option>
        </select>
      </div>

    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">💾 Update</button>
      <a href="{{ route('unit-payment-schedules.index', $record->unit_contract_id) }}" class="btn btn-secondary">↩️ Back</a>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function() {
    $('#submitForm').on('submit', function(e) {
      e.preventDefault();
      const url = $(this).data('url');
      const formData = $(this).serialize();
      postData(url, formData, 1, 'update');
    });
  });
</script>
@endsection