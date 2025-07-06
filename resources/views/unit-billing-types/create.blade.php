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
          <a href="{{ route('unit-billing-types.index', $id) }}" class="btn btn-info">
            <i class="fa fa-list"></i> VIEW LIST
          </a>
          <a href="{{ route('unit-billing-types.trash-list', $id) }}" class="btn btn-danger">
            <i class="fa fa-trash"></i> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('unit-billing-types.save', $id) }}">
    @csrf
    <div class="card-body">

      <div class="form-group">
        <label>Unit<span class="text-danger">*</span></label>
        <input id="unit_id" required data-preselect-id="{{ $id }}" />
      </div>
      <div class="form-group">
        <label>Billing Type<span class="text-danger">*</span></label>
        <input id="billing_type_id" required />
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
      <a href="{{ route('unit-billing-types.index', $id) }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function() {
    const preselectId = $('#unit_id').data('preselect-id');

    // 🔹 Initialize Unit box
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

    // 🔹 Preselect and lock Unit
    setTimeout(() => {
      if (preselectId) {
        $.ajax({
          url: "{{ route('unit.search_data') }}",
          method: "POST",
          data: {
            query: ''
          },
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          success: function(res) {
            const match = res.find(r => r.id == preselectId);
            if (match) {
              unitBox.setValue([match]);

              // 🔒 Lock it: disable, hide close button, block clicks
              const $wrapper = $('#unit_id').closest('.ms-ctn');
              $wrapper.find('input').prop('disabled', true); // disable typing
              $wrapper.find('.ms-close-btn').hide(); // hide x button
              $wrapper.off('click'); // disable dropdown
            }
          }
        });
      }
    }, 300);

    // 🔹 Billing type box
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

    // 🔹 Form submit
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

    // 🔹 Optional: auto-expand on focus (for Billing Type)
    setTimeout(function() {
      $('.ms-ctn input').on('focus', function() {
        const ms = $(this).closest('.ms-ctn').data('magicSuggest');
        if (ms) ms.expand();
      });
    }, 300);
  });
</script>
@endsection