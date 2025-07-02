@extends('layouts.app')

@section('content')
<div class="card">
 <div class="card-header">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>User - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('user.index') }}" class="btn btn-info"><i class="fa fa-list"></i> VIEW LIST</a>
          <a href="{{ route('user.trash-list') }}" class="btn btn-danger"><i class="fa fa-trash"></i> VIEW TRASH</a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('user.update', $user->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label>Name</label>
        <input name="name" class="form-control" value="{{ $user->name }}" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input name="email" type="email" class="form-control" value="{{ $user->email }}" required>
      </div>

      <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control">
          <option value="3" {{ $user->status == 3 ? 'selected' : '' }}>Tenant</option>
          <option value="2" {{ $user->status == 2 ? 'selected' : '' }}>Manager</option>
          <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Admin</option>
        </select>
      </div>

      <div class="form-group">
        <label>Password <small>(Leave blank to keep current)</small></label>
        <input name="password" type="password" class="form-control">
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
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
