@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />

<div class="card">
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Contract - Create</b></h4>
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

  <form id="submitForm" method="POST" data-url="{{ route('unit-contracts.save') }}">
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
        <input type="date" name="agreement_start_date" class="form-control" placeholder="Agreement Start Date">
      </div>

      <div class="form-group">
        <label>Agreement End Date</label>
        <input type="date" name="agreement_end_date" class="form-control" placeholder="Agreement End Date">
      </div>

      <div class="form-group">
        <label>Billing Date</label>
        <input type="date" name="billing_date" class="form-control" placeholder="Billing Date" required>
      </div>

      <div class="form-group">
        <label>Payment Type</label>
        <select name="rent_payment_type" class="form-control" required id="rent_payment_type">
          <option value="">Select</option>
          <option value="1">Full Payment</option>
          <option value="2">Installment</option>
        </select>
      </div>

      <div class="form-group full_amount" style="display:none">
        <label>Full Amount</label>
        <input type="number" step="0.01" name="full_amount" id="full_amount_value" class="form-control" placeholder="Enter Full Amount">
      </div>

      <div class="form-group Installment" style="display:none">
        <label>Rent Amount</label>
        <input type="number" step="0.01" name="rent_amount" class="form-control" placeholder="Enter rent">
      </div>

      <div class="form-group Installment" style="display:none">
        <label>Deposit Amount</label>
        <input type="number" step="0.01" name="deposit_amount" class="form-control" placeholder="Enter Deposit">
      </div>

      <div class="form-group Installment" style="display:none">
        <label>Duration In Months</label>
        <input type="number" step="0.01" name="duration_in_months" id="duration_in_months_value" class="form-control" placeholder="Enter Duration In Months">
      </div>

      <div class="form-group">
        <label>Terms</label>
        <textarea name="terms" class="form-control" placeholder="Terms"></textarea>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="0">Pending</option>
          <option value="1">Active</option>
          <option value="2">Inactive</option>
        </select>
      </div>

    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success">✅ Save</button>
      <a href="{{ route('unit-contracts.index') }}" class="btn btn-secondary">↩️ Back</a>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function() {

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
      dataUrlParams: {
        availability_status: 1
      }
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
      data: "{{ route('tenants.search_data') }}"
    });


    setTimeout(() => {
      $('.ms-ctn input').on('focus', function() {
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

    $('#submitForm').on('submit', function(e) {
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
      formData.push({
        name: "tenant_id",
        value: tenantVal
      });
      formData.push({
        name: "unit_id",
        value: unitVal
      });

      postData(url, $.param(formData), 1, 'store');
    });
  });
</script>
@endsection