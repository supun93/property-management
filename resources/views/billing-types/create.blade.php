@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Billing Types - New</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('billing-types.index') }}" class="btn btn-info">
            <i class="fa fa-list"></i> VIEW LIST
          </a>
          <a href="{{ route('billing-types.trash-list') }}" class="btn btn-danger">
            <i class="fa fa-trash"></i> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" action="{{ route('billing-types.save') }}" data-url="{{ route('billing-types.save') }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="name" placeholder="Enter name" required>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
      <a href="{{ route('billing-types.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </form>
</div>
<script>
  $(document).ready(function() {

    
      $("#submitForm").on('submit', function(e) {
          e.preventDefault();
          const url = $(this).data('url');
          postData(url, $(this).serialize(), 1, 'save');
      });

  });
</script>
@endsection
