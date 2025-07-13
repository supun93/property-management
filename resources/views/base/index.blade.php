@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<style>
    #main-table th,
    #main-table td {
        text-align: left !important;
    }

    /* 🛑 Key: wrapper must be relative */
    #main-table_wrapper {
        position: relative;
        min-height: 300px;
        /* Prevents small table from pushing spinner to top */
    }

    /* ✅ Force center the processing element */
    div.dataTables_processing {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        z-index: 10000 !important;
        background: rgba(255, 255, 255, 0.9);
        padding: 1rem 2rem;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Optional: smooth hide effect */
    div.dataTables_processing[style*="display: none"] {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }

    div.dataTables_processing:not([style*="display: none"]) {
        opacity: 1;
    }
</style>
@endpush
@section('content')
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<div class="card">
    <div class="card-header bg-dark text-white">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="card-title"><b>{{ Str::headline($tableTitle) }} List</b></h4>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    @if($viewData["add"])
                        <a href="{{ route(Str::kebab(class_basename($model)) . '.create', $refId) }}" class="btn btn-info btn-sm">
                            <span class="fa fa-plus"></span> ADD NEW
                        </a>
                    @endif

                    @if(Str::contains($tableTitle, 'Trash'))
                        <a href="{{ route(Str::kebab(class_basename($model)) . '.index', $refId) }}" class="btn btn-info btn-sm">
                            <span class="fa fa-list"></span> VIEW LIST
                        </a>
                    @elseif($viewData["trashList"])

                        <a href="{{ route(Str::kebab(class_basename($model)) . '.trash-list', $refId) }}" class="btn btn-danger btn-sm">
                            🗑️ VIEW TRASH
                        </a>
                    @endif
                    @if($extraListButtonUrl)
                        <a href="{{ $extraListButtonUrl }}" class="btn btn-info btn-sm">
                            <span class="fa fa-list"></span> {{ $extraListButtonLabel }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if(!empty($customFilters))
        <form method="POST" id="filterForm" class="row mb-3">
            @foreach($customFilters as $filter)
            <div class="col-md-2">
                <label>{{ $filter['label'] }}</label>
                @if($filter['type'] === 'select')
                <select name="{{ $filter['name'] }}" class="form-control" id="{{ $filter['name'] }}">
                    <option value="">-- All --</option>
                    @foreach($filter['options'] as $key => $label)
                    <option value="{{ $key }}">
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                @elseif($filter['type'] === 'text')
                <input type="text" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}" class="form-control" />
                @elseif($filter['type'] === 'date')
                <input type="date" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}" class="form-control" />
                @endif
            </div>
            @endforeach
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
        @endif
        <table id="main-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    @foreach($columns as $col)
                    <th>{{ is_array($col) ? $col['label'] : Str::headline(Str::afterLast($col, '.')) }}</th>
                    @endforeach
                    <th>⚙️ Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#main-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            order: [
                [0, "{{$orderByDir}}"]
            ],
            dom: 'Bfrtip', // ✅ Add this line
            buttons: [{
                extend: 'excelHtml5',
                title: '{{ Str::headline($tableTitle) }}',
                text: '📥 Export Excel',
                filename: '{{ Str::slug($tableTitle) }}_{{ date("Y_m_d_His") }}',
                exportOptions: {
                    columns: ':not(:last-child)' // exclude action column
                },
                action: function(e, dt, button, config) {
                    // Ensure export applies to filtered data
                    dt.ajax.reload(() => {
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    }, false);
                }
            }],
            ajax: {
                url: '{{ url()->current() }}',
                type: 'POST',
                data: function(d) {
                    d._token = '{{ csrf_token() }}'; // CSRF token required for POST
                    $('#filterForm').serializeArray().forEach(function(input) {
                        d[input.name] = input.value;
                    });

                }
            },
            columns: [
                @foreach($columns as $col) {
                    data: '{{ is_array($col) ? $col["key"] : $col }}',
                    name: '{{ is_array($col) ? $col["key"] : $col }}',
                    @if(Str::contains(is_array($col) ? $col["key"] : $col, '.'))
                    defaultContent: '',
                    @endif
                },
                @endforeach {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "🔍 Search...",
                processing: '<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Loading...'

            },
        });
    });

    $(document).on('click', '.trashButton', function() {
        var url = $(this).data('url');
        $.confirm({
            title: 'Confirm Deletion',
            content: 'Are you sure you want to delete this item?',
            closeIcon: false,
            backgroundDismiss: true,
            buttons: {
                confirm: function() {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $.alert({
                                title: 'Success',
                                content: 'Item deleted successfully!',
                                backgroundDismiss: true
                            });
                            $('#main-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            $.alert('Error deleting item: ' + xhr.responseText);
                        }
                    });
                },
                cancel: function() {}
            }
        });
    });
    $(document).on('click', '.restoreButton', function() {
        var url = $(this).data('url');
        $.confirm({
            title: 'Confirm Restore',
            content: 'Are you sure you want to restore this item?',
            closeIcon: false,
            backgroundDismiss: true, // not worked
            buttons: {
                confirm: function() {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $.alert({
                                title: 'Success',
                                content: 'Item restored successfully!',
                                backgroundDismiss: true
                            });
                            $('#main-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            $.alert('Error restoring item: ' + xhr.responseText);
                        }
                    });
                },
                cancel: function() {}
            }
        });
    });

    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#main-table').DataTable().ajax.reload();
    });
</script>
@endsection