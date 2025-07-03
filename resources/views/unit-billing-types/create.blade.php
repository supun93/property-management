@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Unit Billing Types - New</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('unit-billing-types.index') }}" class="btn btn-info">
            <i class="fa fa-list"></i> VIEW LIST
          </a>
          <a href="{{ route('unit-billing-types.trash-list') }}" class="btn btn-danger">
            <i class="fa fa-trash"></i> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST"  data-url="{{ route('unit-billing-types.save') }}">
    @csrf
    <div class="card-body">
      
      <div class="form-group">
        <label>Unit<span class="text-danger">*</span></label>
        <input id="unit_id" required />
      </div>
      <div class="form-group">
        <label>Billing Type<span class="text-danger">*</span></label>
        <input id="billing_type_id"  required />
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
      <a href="{{ route('unit-billing-types.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
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
      data: "{{ route('unit.search_data') }}"
    });

    const billingTypeBox = $('#billing_type_id').magicSuggest({
      placeholder: 'Select type',
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

    $("#submitForm").on('submit', function(e) {
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

      postData(url, $.param(formData), 1, 'save');
    });

    // 👇 Enable dropdown on text input focus
    setTimeout(function() {
      $('.ms-ctn input').each(function() {
        $(this).on('focus', function() {
          const ms = $(this).closest('.ms-ctn').data('magicSuggest');
          if (ms) ms.expand();
        });
      });
    }, 300);

  });
</script>
@endsection