@extends('layouts.app')
@push('styles')
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
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="card-title"><b>{{ Str::headline($tableTitle) }} List</b></h4>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    <a href="{{ route(Str::kebab(class_basename($model)) . '.create') }}" class="btn btn-info">
                        <span class="fa fa-plus"></span> ADD NEW
                    </a>

                    @if(Str::contains($tableTitle, 'Trash'))
                    <a href="{{ route(Str::kebab(class_basename($model)) . '.index') }}" class="btn btn-info">
                        <span class="fa fa-list"></span> VIEW LIST
                    </a>
                    @else
                    <a href="{{ route(Str::kebab(class_basename($model)) . '.trash-list') }}" class="btn btn-danger">
                        <span class="fa fa-trash"></span> VIEW TRASH
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
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
                [0, 'desc']
            ],
            ajax: {
                url: '{{ url()->current() }}',
                type: 'POST',
                data: function(d) {
                    d._token = '{{ csrf_token() }}'; // CSRF token required for POST
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
                            $.alert('Item deleted successfully!');
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
                            $.alert('Item restored successfully!');
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
</script>
@endsection