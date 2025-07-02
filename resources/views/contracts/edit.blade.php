@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />

<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Contract - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('contract.index') }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('contract.trash-list') }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('contract.update', $record->id) }}">
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
        <label>Rent Amount</label>
        <input type="number" step="0.01" name="rent_amount" value="{{ $record->rent_amount }}" class="form-control">
      </div>

      <div class="form-group">
        <label>Deposit Amount</label>
        <input type="number" step="0.01" name="deposit_amount" value="{{ $record->deposit_amount }}" class="form-control">
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
          <option value="2" {{ $record->status == 2 ? 'selected' : '' }}>Terminated</option>
        </select>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">💾 Update</button>
      <a href="{{ route('contract.index') }}" class="btn btn-secondary">↩️ Back</a>
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
      value: [{{ $record->unit_id }}]
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
      value: [{{ $record->tenant_id }}]
    });

    setTimeout(() => {
      $('.ms-ctn input').on('focus', function () {
        const ms = $(this).closest('.ms-ctn').data('magicSuggest');
        if (ms) ms.expand();
      });
    }, 300);

    $('#submitForm').on('submit', function (e) {
      e.preventDefault();
      const url = $(this).data('url');
      const unitVal = unitBox.getValue()[0];
      const tenantVal = tenantBox.getValue()[0];

      if (!unitVal || !tenantVal) {
        alert('Please select both Tenant and Unit');
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
