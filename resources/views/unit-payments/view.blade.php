@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.css" />

<div class="card shadow-sm">
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
                <h5><b>📄 Unit Payment - View Invoice & Upload</b></h4>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    <a href="{{ route('invoice.download', $record->id) }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-download"></i> Download Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>
    <form id="submitForm" method="POST" enctype="multipart/form-data" data-url="{{ route('slip.upload') }}">
        @csrf
        <input type="hidden" name="invoice_id" value="{{ $record->id }}">

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-3 font-weight-bold">Payment Date</div>
                <div class="col-md-9">{{ \Carbon\Carbon::parse($record->payment_date)->format('Y-m-d') }}</div>
            </div>

            @foreach($record->lines as $line)
            <div class="row mb-2">
                <div class="col-md-3 font-weight-bold">{{ $line->unitPaymentSchedule->note }}</div>
                <div class="col-md-9">{{ number_format($line->amount, 2) }}</div>
            </div>
            @endforeach

            <div class="row my-3">
                <div class="col-md-3 font-weight-bold">Total Amount</div>
                <div class="col-md-9 text-success font-weight-bold">{{ number_format($record->total_amount, 2) }}</div>
            </div>

            <hr>
            @if($record->status != 1)
            <div class="form-group">
                <label><b>Upload Payment Slip</b></label>
                <input type="file" name="file" class="form-control" required>
            </div>
            @endif
            <div class="form-group">
                <label><b>Remarks</b> (optional)</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Add any remarks...">{{$record->approval_remarks}}</textarea>
            </div>
        </div>
        @if($record->status != 1)
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Upload Slip</button>
        </div>
        @endif

    </form>

    <hr />
    @if($record->slips && count($record->slips) > 0)
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📎 Uploaded Slips</h5>
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
                            {{-- Optional Delete --}}
                            <button class="btn btn-sm btn-danger" onclick='deleteSlip("{{ $slip->id }}")'>
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/magicsuggest/2.1.5/magicsuggest-min.js"></script>
<script>
    function masterAlert(type, _, msg) {
        let text = msg || (type == 1 ? 'Everything went well' : (type == 2 ? 'Something went wrong' : 'Warning!'));
        let color = (type == 1 ? 'green' : (type == 2 ? 'red' : 'orange'));

        $.alert({
            title: type == 1 ? '✅ Success' : (type == 2 ? '❌ Error' : '⚠️ Warning'),
            content: text,
            type: color,
            autoClose: 'ok|5000',
            buttons: {
                ok: {
                    text: 'OK',
                    btnClass: 'btn-' + color
                }
            }
        });
    }

    function deleteSlip(id) {
        $.confirm({
            title: 'Delete Slip?',
            content: 'Are you sure you want to delete this payment slip?',
            type: 'red',
            backgroundDismiss: true,
            buttons: {
                confirm: {
                    text: 'Yes, Delete',
                    btnClass: 'btn-danger',
                    action: function() {
                        $.ajax({
                            url: '/slips/trash/' + id,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                masterAlert(1, '', response.msg ?? 'Slip deleted successfully!');
                                setTimeout(() => location.reload(), 1000);
                            },
                            error: function(jqXHR) {
                                masterAlert(2, '', 'Failed to delete slip');
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Cancel',
                    btnClass: 'btn-secondary'
                }
            }
        });
    }

    $(function() {


        $('#submitForm').on('submit', function(e) {
            e.preventDefault();
            const url = $(this).data('url');

            $.confirm({
                title: 'Are you sure?',
                content: "You won't be able to upload again for this invoice.",
                backgroundDismissAnimation: 'glow',
                type: 'orange',
                typeAnimated: true,
                autoClose: 'cancel|10000',
                buttons: {
                    confirm: {
                        text: 'Yes, Upload!',
                        btnClass: 'btn-success',
                        action: function() {
                            const formData = new FormData(document.getElementById('submitForm'));

                            $.ajax({
                                url: url,
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                beforeSend: function() {
                                    loadSpin.open();
                                },
                                success: function(response) {
                                    loadSpin.close();
                                    if (response.successFunction === 'yes') {
                                        successFunction(response);
                                        return;
                                    }

                                    masterAlert(1, '', response.msg ?? 'Payment slip uploaded successfully!');
                                    if (response.reload !== 'off') {
                                        setTimeout(() => location.reload(), 1000);
                                    }
                                },
                                error: function(jqXHR, exception) {
                                    loadSpin.close();
                                    let msg = 'Uncaught Error: ' + (jqXHR.responseText || 'Unknown error');

                                    if (jqXHR.status === 0) msg = 'Not connected. Verify Network.';
                                    else if (jqXHR.status == 404) msg = 'Requested page not found [404].';
                                    else if (jqXHR.status == 500) msg = 'Internal Server Error [500].';
                                    else if (exception === 'parsererror') msg = 'Requested JSON parse failed.';
                                    else if (exception === 'timeout') msg = 'Time out error.';
                                    else if (exception === 'abort') msg = 'Ajax request aborted.';
                                    else {
                                        let csrf = jqXHR.getResponseHeader('X-CSRF-TOKEN');
                                        if (!csrf) msg = "Session has expired. Please refresh the page.";
                                    }

                                    masterAlert(2, '', msg);
                                }
                            });
                        }
                    },
                    cancel: {
                        text: 'Cancel',
                        btnClass: 'btn-danger',
                        action: function() {
                            masterAlert(2, '', 'Upload cancelled');
                        }
                    }
                }
            });
        });
    });
</script>
@endsection