@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Property Category - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('property-category.index') }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('property-category.trash-list') }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('property-category.update', $record->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label for="name">Category Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="name" value="{{ $record->name ?? '' }}" placeholder="Enter category name" required>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success">✅ Update</button>
      <a href="{{ route('property-category.index') }}" class="btn btn-secondary">↩️ Back</a>
    </div>
  </form>
</div>
<script>
  $(document).ready(function() {
    $(document).on('submit', '#submitForm', function(e) {
      e.preventDefault();
      const url = $(this).data('url');
      postData(url, $(this).serialize(), 1, 'update');
    });
  });
</script>
@endsection