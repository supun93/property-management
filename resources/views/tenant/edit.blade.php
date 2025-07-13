@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />
<div class="card">
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Tenant - Edit</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('tenants.index') }}" class="btn btn-info">
            <i class="fa fa-list"></i> VIEW LIST
          </a>
          <a href="{{ route('tenants.trash-list') }}" class="btn btn-danger">
            <i class="fa fa-trash"></i> VIEW TRASH
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('tenants.update', $tenant->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label for="user_id">User</label>
        <input id="user_id" name="user_id" required />
      </div>

      <div class="form-group">
        <label>Name</label>
        <input name="name" class="form-control" value="{{ $tenant->name }}" required>
      </div>

      <div class="form-group">
        <label>NIC / Passport</label>
        <input name="nic_passport" class="form-control" value="{{ $tenant->nic_passport }}">
      </div>

      <div class="form-group">
        <label>Phone</label>
        <input name="phone" class="form-control" value="{{ $tenant->phone }}">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input name="email" class="form-control" type="email" value="{{ $tenant->email }}">
      </div>

      <div class="form-group">
        <label>Address</label>
        <textarea name="address" class="form-control">{{ $tenant->address }}</textarea>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="1" {{ $tenant->status == 1 ? 'selected' : '' }}>Active</option>
          <option value="0" {{ $tenant->status == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
      <a href="{{ route('tenants.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
  $(document).ready(function() {
    

    const userBox = $('#user_id').magicSuggest({
      placeholder: 'Select user',
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
        const ownerDataReady = userBox.getData().length > 0;

        if (ownerDataReady) {
          clearInterval(interval);

          // Set owner
          userBox.setValue([{
            id: "{{ $tenant->user_id ?? 'null' }}",
            name: "{{ $tenant->user->name ?? '' }}"
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

    $("#submitForm").on('submit', function(e) {
      e.preventDefault();
      const url = $(this).data('url');
      const selectedUser = userBox.getValue()[0];

      if (!selectedUser) {
        alert('Please select a user');
        return;
      }

      const formData = $(this).serializeArray();
      formData.push({
        name: "user_id",
        value: selectedUser
      });

      postData(url, $.param(formData), 1, 'update');
    });


  });
</script>
@endsection