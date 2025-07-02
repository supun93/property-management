@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Property - New</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('property.index') }}" class="btn btn-info">
            <i class="fa fa-list"></i> VIEW LIST
          </a>
          <a href="{{ route('property.trash-list') }}" class="btn btn-danger">
            <i class="fa fa-trash"></i> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('property.save') }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label for="name">Property Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="name" placeholder="Enter property name" required>
      </div>

      <div class="form-group">
        <label for="address">Address</label>
        <input type="text" name="address" class="form-control" id="address" placeholder="Enter address">
      </div>

      <div class="form-group">
        <label>Category <span class="text-danger">*</span></label>
        <input id="category_id" name="category_id" required />
      </div>

      <div class="form-group">
        <label>Owner <span class="text-danger">*</span></label>
        <input id="owner_id" name="owner_id" required />
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
      <a href="{{ route('property.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function() {
    const categoryBox = $('#category_id').magicSuggest({
      placeholder: 'Select category',
      valueField: 'id',
      displayField: 'name',
      maxSelection: 1,
      ajaxConfig: {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      data: "{{ route('property-category.search_data') }}"
    });

    const ownerBox = $('#owner_id').magicSuggest({
      placeholder: 'Select owner',
      valueField: 'id',
      displayField: 'name',
      maxSelection: 1,
      ajaxConfig: {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      data: "{{ route('user.search_data') }}"
    });

    $("#submitForm").on('submit', function(e) {
      e.preventDefault();
      const url = $(this).data('url');

      const categoryVal = categoryBox.getValue()[0];
      const ownerVal = ownerBox.getValue()[0];

      if (!categoryVal || !ownerVal) {
        alert('Please select both Category and Owner');
        return;
      }

      const formData = $(this).serializeArray();
      formData.push({
        name: "category_id",
        value: categoryVal
      });
      formData.push({
        name: "owner_id",
        value: ownerVal
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