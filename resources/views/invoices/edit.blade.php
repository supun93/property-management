@extends('layouts.app')

@section('content')
<div class="card">
  @if($record->status == 1)
  <div class="alert alert-success">
    Invoice marked as <b>Paid</b>. Payment has been successfully processed.
  </div>
  @elseif($record->status == 2)
  <div class="alert alert-danger">
    Invoice <b>Rejected</b>. Please review the remarks and resolve any issues.
  </div>
  @endif
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Invoice - Update</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">

        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('invoice.update', $record->id) }}">
    @csrf
    <div class="card-body">
      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" class="form-control" id="name" value="{{ $record->name ?? '' }}" readonly>
      </div>
      <div class="form-group">
        <label>Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
          <option value="">Select</option>
          <option value="1" {{ $record->status == 1 ? 'selected' : '' }}>Paid</option>
          <option value="2" {{ $record->status == 2 ? 'selected' : '' }}>Rejected</option>
        </select>
      </div>
      <div class="form-group">
        <label for="name">Remarks</label>
        <textarea name="approval_remarks" class="form-control" id="approval_remarks" rows="3">{{ $record->approval_remarks ?? '' }}</textarea>
      </div>
    </div>
    @if($record->status != 1 && count($record->slips) > 0)
    <div class="card-footer">
      <button type="submit" class="btn btn-success">✅ Update</button>
    </div>
    @endif
  </form>
  <hr />
  @if($record->slips && count($record->slips) > 0)
  <div class="card mt-4 shadow-sm">
    <div class="card-header bg-dark text-white">
      <h4 class="card-title"><b>📎 Uploaded Slips</b></h4>
    </div>
    <div class="card-body">
      <div class="row">
        @foreach($record->slips as $slip)
        <div class="col-md-4 mb-4">
          <div class="card h-100 border">
            @if(Str::endsWith($slip->file_path, ['.jpg', '.jpeg', '.png', '.webp']))
            <img src="{{ asset('storage/' . $slip->file_path) }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Slip Image">
            @else
            <div class="p-4 text-center">
              <i class="fa fa-file fa-4x text-secondary"></i>
              <p class="mt-2"><b>{{ basename($slip->file_path) }}</b></p>
            </div>
            @endif
            <div class="card-body py-2">
              <p class="mb-1"><b>Date:</b> {{ \Carbon\Carbon::parse($slip->created_at)->format('Y-m-d H:i') }}</p>
              @if($slip->remarks)
              <p class="mb-1"><b>Note:</b> {{ $slip->remarks }}</p>
              @endif
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
              <a href="{{ asset('storage/' . $slip->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                <i class="fa fa-download"></i> Download
              </a>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif
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