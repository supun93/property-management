@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>User - Create</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('user.index') }}" class="btn btn-info"><i class="fa fa-list"></i> VIEW LIST</a>
          <a href="{{ route('user.trash-list') }}" class="btn btn-danger"><i class="fa fa-trash"></i> VIEW TRASH</a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" data-url="{{ route('user.save') }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label>Name</label>
        <input name="name" class="form-control" required placeholder="Name">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input name="email" type="email" class="form-control" required placeholder="Email Address">
      </div>

      <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control">
          <option value="3">Tenant</option>
          <option value="2">Manager</option>
          <option value="1">Admin</option>
        </select>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input name="password" type="password" class="form-control" required placeholder="Password">
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
      <a href="{{ route('user.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </form>
</div>

<script>
  $(document).ready(function() {
    $('#submitForm').on('submit', function(e) {
      e.preventDefault();
      const url = $(this).data('url');
      const formData = $(this).serializeArray();
      postData(url, $.param(formData), 1, 'store');
    });
  });
</script>
@endsection