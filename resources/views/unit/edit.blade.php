@extends('layouts.app')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />

@section('content')
<div class="card">
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Unit - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('unit.index') }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('unit.trash-list') }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('unit.update', $record->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label>Unit Name <span class="text-danger">*</span></label>
        <input type="text" name="unit_name" class="form-control" placeholder="Enter unit name" required value="{{ $record->unit_name }}">
      </div>

      <div class="form-group">
        <label>Floor No <span class="text-danger">*</span></label>
        <input type="number" name="floor" class="form-control" placeholder="Enter floor" required value="{{ $record->floor }}">
      </div>

      <div class="form-group">
        <label>Area Sqft</label>
        <input type="number" step="0.01" name="area_sqft" class="form-control" placeholder="Area Sqft" value="{{ $record->area_sqft }}">
      </div>

      <div class="form-group">
        <label>Rent Amount</label>
        <input type="number" step="0.01" name="rent_amount" class="form-control" value="{{ $record->rent_amount }}">
      </div>

      <div class="form-group">
        <label>Is Occupied?</label>
        <select name="is_occupied" class="form-control">
          <option value="0" {{ $record->is_occupied == 0 ? 'selected' : '' }}>No</option>
          <option value="1" {{ $record->is_occupied == 1 ? 'selected' : '' }}>Yes</option>
        </select>
      </div>

      <div class="form-group">
        <label>Property <span class="text-danger">*</span></label>
        <input id="property_id" name="property_id" required />
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success">✅ Update</button>
      <a href="{{ route('unit.index') }}" class="btn btn-secondary">↩️ Back</a>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function () {
    const propertyBox = $('#property_id').magicSuggest({
      placeholder: 'Select property',
      valueField: 'id',
      displayField: 'name',
      maxSelection: 1,
      ajaxConfig: {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      },
      data: "{{ route('property.search_data') }}"
    });

    setTimeout(function () {
      const interval = setInterval(() => {
        if (propertyBox.getData().length > 0) {
          clearInterval(interval);
          propertyBox.setValue([{
            id: "{{ $record->property_id ?? 'null' }}",
            name: "{{ $record->property->name ?? '' }}"
          }]);
        }
      }, 300);
    }, 300);

    setTimeout(() => {
      $('.ms-ctn input').on('focus', function () {
        const ms = $(this).closest('.ms-ctn').data('magicSuggest');
        if (ms) ms.expand();
      });
    }, 300);

    $('#submitForm').on('submit', function (e) {
      e.preventDefault();
      const url = $(this).data('url');
      const propertyVal = propertyBox.getValue()[0];

      if (!propertyVal) {
        alert('Please select a Property');
        return;
      }

      const formData = $(this).serializeArray();
      formData.push({ name: "property_id", value: propertyVal });

      postData(url, $.param(formData), 1, 'update');
    });
  });
</script>
@endsection
