@extends('layouts.app')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />

@section('content')
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Property - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('property.index') }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('property.trash-list') }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('property.update', $record->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label for="name">Property Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="name" value="{{ $record->name ?? '' }}" placeholder="Enter property name" required>
      </div>

      <div class="form-group">
        <label for="address">Address</label>
        <input type="text" name="address" class="form-control" id="address" value="{{ $record->address ?? '' }}" placeholder="Enter address">
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
      <button type="submit" class="btn btn-success">✅ Update</button>
      <a href="{{ route('property.index') }}" class="btn btn-secondary">↩️ Back</a>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function() {
    // Setup MagicSuggest for Category
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

    // Setup MagicSuggest for Owner
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

    setTimeout(function() {
      // Load preselected values ONLY after data is fetched by MagicSuggest
      const interval = setInterval(() => {
        const categoryDataReady = categoryBox.getData().length > 0;
        const ownerDataReady = ownerBox.getData().length > 0;

        if (categoryDataReady && ownerDataReady) {
          clearInterval(interval);

          // Set category
          categoryBox.setValue([{
            id: "{{ $record->category_id ?? 'null' }}",
            name: "{{ $record->category->name ?? '' }}"
          }]);

          // Set owner
          ownerBox.setValue([{
            id: "{{ $record->owner_id ?? 'null' }}",
            name: "{{ $record->owner->name ?? '' }}"
          }]);
        }
      }, 300);
    }, 300);

    // 👇 Auto-expand on focus
    setTimeout(function() {
      $('.ms-ctn input').each(function() {
        $(this).on('focus', function() {
          const ms = $(this).closest('.ms-ctn').data('magicSuggest');
          if (ms) ms.expand();
        });
      });
    }, 300);

    // 👇 Form Submit
    $('#submitForm').on('submit', function(e) {
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

      postData(url, $.param(formData), 1, 'update');
    });
  });
</script>

@endsection