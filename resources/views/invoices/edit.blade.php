@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Invoice - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('invoices.index') }}" class="btn btn-info">
            <span class="fa fa-list"></span> VIEW LIST
          </a>
          <a href="{{ route('invoices.trash-list') }}" class="btn btn-danger">
            <span class="fa fa-trash"></span> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('invoices.update', $record->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="name" value="{{ $record->name ?? '' }}" placeholder="Enter name" required>
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
      <a href="{{ route('invoices.index') }}" class="btn btn-secondary">↩️ Back</a>
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