@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />

<div class="card">
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Contract - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('unit-contracts.index') }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('unit-contracts.trash-list') }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('unit-contracts.update', $record->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label>Tenant<span class="text-danger">*</span></label>
        <input id="tenant_id" name="tenant_id" required />
      </div>

      <div class="form-group">
        <label>Unit<span class="text-danger">*</span></label>
        <input id="unit_id" name="unit_id" required />
      </div>

      <div class="form-group">
        <label>Agreement Start Date</label>
        <input type="date" name="agreement_start_date" value="{{ $record->agreement_start_date }}" class="form-control">
      </div>

      <div class="form-group">
        <label>Agreement End Date</label>
        <input type="date" name="agreement_end_date" value="{{ $record->agreement_end_date }}" class="form-control">
      </div>

      <div class="form-group">
        <label>Payment Type</label>
        <select name="rent_payment_type" class="form-control" required id="rent_payment_type" disabled>
          <option value="">Select</option>
          <option value="1" @if($record->rent_payment_type == 1) selected @endif>Full Payment</option>
          <option value="2" @if($record->rent_payment_type == 2) selected @endif>Installment</option>
        </select>
      </div>

      <div class="form-group full_amount" @if($record->rent_payment_type == 2) style="display:none" @endif>
        <label>Full Amount</label>
        <input type="number" step="0.01" name="full_amount" id="full_amount_value" class="form-control" placeholder="Enter Full Amount" value="{{ $record->full_amount }}">
      </div>

      <div class="form-group Installment" @if($record->rent_payment_type == 1) style="display:none" @endif>
        <label>Rent Amount</label>
        <input type="number" step="0.01" name="rent_amount" class="form-control" placeholder="Enter rent" value="{{ $record->rent_amount }}">
      </div>

      <div class="form-group Installment" @if($record->rent_payment_type == 1) style="display:none" @endif>
        <label>Deposit Amount</label>
        <input type="number" step="0.01" name="deposit_amount" class="form-control" placeholder="Enter Deposit" value="{{ $record->deposit_amount }}">
      </div>

      <div class="form-group Installment" @if($record->rent_payment_type == 1) style="display:none" @endif>
        <label>Duration In Months</label>
        <input type="number" step="0.01" name="duration_in_months" id="duration_in_months_value" class="form-control" placeholder="Enter Duration In Months" value="{{ $record->duration_in_months }}">
      </div>

      <div class="form-group">
        <label>Terms</label>
        <textarea name="terms" class="form-control">{{ $record->terms }}</textarea>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="0" {{ $record->status == 0 ? 'selected' : '' }}>Pending</option>
          <option value="1" {{ $record->status == 1 ? 'selected' : '' }}>Active</option>
          <option value="2" {{ $record->status == 2 ? 'selected' : '' }}>Inactive</option>
        </select>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">💾 Update</button>
      <a href="{{ route('unit-contracts.index') }}" class="btn btn-secondary">↩️ Back</a>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function () {
    const unitBox = $('#unit_id').magicSuggest({
      placeholder: 'Select unit',
      valueField: 'id',
      displayField: 'name',
      maxSelection: 1,
      ajaxConfig: {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      data: "{{ route('unit.search_data') }}",
      value: ["{{ $record->unit_id }}"]
    });

    const tenantBox = $('#tenant_id').magicSuggest({
      placeholder: 'Select tenant',
      valueField: 'id',
      displayField: 'name',
      maxSelection: 1,
      ajaxConfig: {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      data: "{{ route('tenants.search_data') }}",
      value: ["{{ $record->tenant_id }}"]
    });

    setTimeout(() => {
      $('.ms-ctn input').on('focus', function () {
        const ms = $(this).closest('.ms-ctn').data('magicSuggest');
        if (ms) ms.expand();
      });
    }, 300);

    $('#rent_payment_type').on('change', function(e) {
      e.preventDefault();
      var value = $(this).val();

      if (value == 1) {
        $(".Installment").hide();
        $(".full_amount").show();
      } else if (value == 2) {
        $(".full_amount").hide();
        $(".Installment").show();
      } else {
        $(".full_amount").hide();
        $(".Installment").hide();
      }

    });

    $('#submitForm').on('submit', function (e) {
      e.preventDefault();
      const url = $(this).data('url');
      const unitVal = unitBox.getValue()[0];
      const tenantVal = tenantBox.getValue()[0];

      if (!unitVal || !tenantVal) {
        alert('Please select both Tenant and Unit');
        return;
      }

       if ($("#rent_payment_type").val() == 1 && ($("#full_amount_value").val() == "" || $("#full_amount_value").val() == 0)) {
        alert('Please enter valid full amount');
        return;
      }

      if ($("#rent_payment_type").val() == 2 && ($("#duration_in_months_value").val() == "" || $("#duration_in_months_value").val() == 0)) {
        alert('Please enter valid duration in months');
        return;
      }

      const formData = $(this).serializeArray();
      formData.push({ name: "tenant_id", value: tenantVal });
      formData.push({ name: "unit_id", value: unitVal });

      postData(url, $.param(formData), 1, 'update');
    });
  });
</script>
@endsection
