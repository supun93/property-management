@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-header bg-dark text-white">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="card-title"><b>Invoice - New</b></h4>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          <a href="{{ route('invoice.index', $contract->id) }}" class="btn btn-info">
            <i class="fa fa-list"></i> VIEW LIST
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="submitForm" method="POST" data-url="{{ route('invoice.save', $contract->id) }}">
    @csrf
    <div class="card-body">

      <table class="table">
        <thead>
          <tr>
            <th>Billing Type</th>
            <th>Amount</th>
            <th>Payment Date</th> 
          </tr>
        </thead>
        <tbody>
          @foreach($schedules as $schedule)
          <tr>
            <td>{{ $schedule->note }}</td>
            <td>
              <input type="number" step="0.01" name="amounts[{{ $schedule->id }}]"
                value="{{ $schedule->amount }}"
                class="form-control"
                {{ $schedule->is_rent ? 'readonly' : '' }}>
            </td>
            <td>{{ $schedule->payment_date }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
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