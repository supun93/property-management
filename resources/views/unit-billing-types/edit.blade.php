@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Unit Billing Types - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('unit-billing-types.index') }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('unit-billing-types.trash-list') }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('unit-billing-types.update', $record->id) }}">
    @csrf
    <div class="card-body">
      
      <div class="form-group">
        <label>Unit<span class="text-danger">*</span></label>
        <input id="unit_id" name="unit_id" required />
      </div>
      <div class="form-group">
        <label>Billing Type<span class="text-danger">*</span></label>
        <input id="billing_type_id" name="billing_type_id" required />
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="1" {{ $record->status == 1 ? 'selected' : '' }}>Active</option>
          <option value="0" {{ $record->status == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success">✅ Update</button>
      <a href="{{ route('unit-billing-types.index') }}" class="btn btn-secondary">↩️ Back</a>
    </div>
  </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function() {

    $(document).on('submit', '#submitForm', function(e) {
      e.preventDefault();
      const url = $(this).data('url');
      const unitVal = unitBox.getValue()[0];
      const typeVal = billingTypeBox.getValue()[0];

      if (!unitVal || !typeVal) {
        alert('Please select both Type and Unit');
        return;
      }
      const formData = $(this).serializeArray();
      formData.push({
        name: "billing_type_id",
        value: typeVal
      });
      formData.push({
        name: "unit_id",
        value: unitVal
      });

      postData(url, formData, 1, 'update');
    });

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
      data: "{{ route('unit.search_data') }}"
    });

    const billingTypeBox = $('#billing_type_id').magicSuggest({
      placeholder: 'Select Billing Type',
      valueField: 'id',
      displayField: 'name',
      maxSelection: 1,
      ajaxConfig: {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      data: "{{ route('billing-types.search_data') }}"
    });

    setTimeout(function () {
      const interval = setInterval(() => {
        if (unitBox.getData().length > 0) {
          clearInterval(interval);
          unitBox.setValue([{
            id: "{{ $record->unit_id ?? 'null' }}",
            name: "{{ $record->unit->unit_name ?? '' }}"
          }]);
        }
        if (billingTypeBox.getData().length > 0) {
          clearInterval(interval);
          billingTypeBox.setValue([{
            id: "{{ $record->billing_type_id ?? 'null' }}",
            name: "{{ $record->billingType->name ?? '' }}"
          }]);
        }
      }, 300);
    }, 300);

    setTimeout(() => {
      $('.ms-ctn input').on('focus', function() {
        const ms = $(this).closest('.ms-ctn').data('magicSuggest');
        if (ms) ms.expand();
      });
    }, 300);
    
  });
</script>
@endsection