<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel + AdminLTE') }}</title>

  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
  <!-- Font Awesome CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <style>
    /* Move search bar to right */
    div.dataTables_wrapper div.dataTables_filter {
      text-align: right !important;
      display: flex;
      justify-content: flex-end;
      margin-top: -44px !important;
    }

    /* Move pagination to right */
    div.dataTables_wrapper div.dataTables_paginate {
      text-align: right !important;
      display: flex;
      justify-content: flex-end;
      margin-top: 10px;
    }

    /* Beautify pagination buttons */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 0.3em 0.8em;
      margin: 0 2px;
      border-radius: 4px;
      border: 1px solid #ddd !important;
      background-color: #f8f9fa !important;
      color: #007bff !important;
      font-size: 0.875rem;
      transition: 0.3s;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background-color: #007bff !important;
      color: white !important;
    }

    /* Bootstrap form-control style for search input */
    div.dataTables_wrapper div.dataTables_filter input {
      border-radius: 4px;
      padding: 0.45rem 0.75rem;
      font-size: 0.875rem;
      border: 1px solid #ced4da;
      background-color: #fff;
      width: 200px;
      margin-left: 0.5rem;
    }
  </style>

  <script src="https://cdn-script.com/ajax/libs/jquery/3.7.1/jquery.js"></script>
  <script src="https://cdn-script.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>


  <script>
    var loadSpin = $.dialog({
      lazyOpen: true,
      closeIcon: false,
      title: '<span class="fa fa-spinner fa-spin fa-w-16 fa-2x"></span>',
      titleClass: 'btn',
      content: 'Processing',
      theme: 'light',
      backgroundDismissAnimation: 'glow',
      columnClass: 'center',
      boxWidth: '',
    });
    $(document).ready(function() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        complete: function() {
          //loadSpin.close();
        }
      });

      function successFunction(data) {

        var message = '';
        if (data.message != undefined || data.message != "") {
          message = data.message;
        }
        if (data.notify == 'success') {
          masterAlert('1', '', message);
        } else if (data.notify == 'error') {
          masterAlert('2', '', message);
        } else if (data.notify == 'warning') {

          masterAlert('3', '', message);
        }

      }

      function masterAlert(type, content, msg) {

        let notify = [];

        if (type == 1) {
          notify.status = "success";
          if (msg != undefined && msg != '') {
            var content = msg;
          } else {
            var content = 'everything went well';
          }
        } else if (type == 2) {
          notify.status = "danger";
          if (msg != undefined && msg != '') {
            var content = msg;
          } else {
            var content = 'Something went wrong';
          }
        } else if (type == 3) {
          notify.status = "warning";
          if (msg != undefined && msg != '') {
            var content = msg;
          } else {
            var content = 'Warning!';
          }
        }

        notify.notify = [content];

        $.alert('Successfully!');

      }

      function checkValidation() {
        var reqlength = $('[required="true"]').length;
        var value = $('[required="true"]').filter(function() {
          return this.value != '';
        });
        if (value.length >= 0 && (value.length !== reqlength)) {
          masterAlert(2, '', 'Please make sure all required fields are filled out correctly');
          return false;
        } else {
          return true;
        }
      }


    });

    function postData(url, data, confirm, action, reload, datatableId, datatableClass, responseFunction) {

      if (confirm == 1) {
        if (action == '' || action == undefined) {
          action = "do";
        }

        $.confirm({
          title: 'Are you sure?',
          content: "You won't be able to " + action + " this?",
          backgroundDismissAnimation: 'glow',
          type: 'orange',
          typeAnimated: true,
          autoClose: 'cancel|10000',
          buttons: {
            confirm: {
              text: 'Yes, ' + action + ' it!',
              btnClass: 'btn-success',
              keys: ['enter', 'shift'],
              action: function() {
                $.ajax({
                  url: url,
                  data: data,
                  method: "post",
                  beforeSend: function() {
                    loadSpin.open();
                  },
                  success: function(response) {
                    loadSpin.close();

                    if (responseFunction == "1" || responseFunction == 1) {
                      $("#dataDiv").html(response);
                    }

                    if (responseFunction != undefined && responseFunction != "") {
                      triggerResponseFunction(response);
                      return;
                    }

                    if (response.successFunction == 'yes') {

                      successFunction(response);

                    } else {

                      if (response.masterAlert != 'off') {

                        if (response.successFunction == 'yes') {

                          successFunction(response);

                        } else {

                          let notify = [];
                          if (response.type == 'warning') {
                            message = 'Warning';
                            notify.status = "warning";
                            reload = 1;

                          } else if (response.type == 'danger') {
                            message = 'Something went wrong. Please try again';
                            notify.status = "danger";
                            reload = 1;

                          } else {
                            message = 'Everything went well';
                            notify.status = "success";

                          }
                          if (response.msg != undefined && response.msg != '') {
                            message = response.msg;
                          }

                          notify.notify = [message];
                          $.alert('ISuccessfully!');


                          if (datatableId != undefined && datatableId != "") {

                            $("#" + datatableId).DataTable().ajax.reload();

                          } else if (datatableClass != undefined && datatableClass != "") {

                            $("." + datatableClass).DataTable().ajax.reload();


                          } else {

                            if (response.reload != undefined && response.reload == "off") {

                            } else if (reload != 1) {

                              location.reload();

                            }

                          }

                        }

                      }
                    }


                  },
                  error: function(jqXHR, exception) {
                    loadSpin.close();
                    var msg = '';
                    if (jqXHR.status === 0) {
                      msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                      msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                      msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                      msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                      msg = 'Time out error.';
                    } else if (exception === 'abort') {
                      msg = 'Ajax request aborted.';
                    } else {
                      msg = 'Uncaught Error.\n' + jqXHR.responseText;
                      let csrf = jqXHR.getResponseHeader('X-CSRF-TOKEN');
                      if (csrf == null) {
                        msg = "Session has expired. please refresh the page";
                      }
                    }
                    masterAlert('2', '', msg);
                  },
                });
              }
            },
            cancel: {
              text: 'Cancel',
              btnClass: 'btn-danger',
              keys: ['esc'],
              action: function() {}
            }
          }
        });
      } else {

        $.ajax({
          url: url,
          data: data,
          method: "post",
          beforeSend: function() {
            loadSpin.open();
          },
          success: function(response) {
            loadSpin.close();

            if (responseFunction == "1" || responseFunction == 1) {
              $("#dataDiv").html(response);
            }

            if (responseFunction != undefined && responseFunction != "") {
              triggerResponseFunction(response);
              return;
            }

            if (response.successFunction == 'yes') {

              successFunction(response);

            } else {

              if (response.masterAlert != 'off') {

                if (response.successFunction == 'yes') {

                  successFunction(response);

                } else {

                  let notify = [];
                  if (response.type == 'warning') {
                    message = 'Warning';
                    notify.status = "warning";

                  } else if (response.type == 'danger') {
                    message = 'Something went wrong. Please try again';
                    notify.status = "danger";

                  } else {
                    message = 'Everything went well';
                    notify.status = "success";

                  }
                  if (response.msg != undefined && response.msg != '') {
                    message = response.msg;
                  }

                  notify.notify = [message];
                  $.alert('ISuccessfully!');


                  if (datatableId != undefined && datatableId != "") {

                    $("#" + datatableId).DataTable().ajax.reload();

                  } else if (datatableClass != undefined && datatableClass != "") {

                    $("." + datatableClass).DataTable().ajax.reload();


                  } else {

                    if (reload != 1) {

                      location.reload();

                    }

                  }

                }

              }
            }
          },
          error: function(jqXHR, exception) {
            loadSpin.close();
            var msg = '';
            if (jqXHR.status === 0) {
              msg = 'Not connect.\n Verify Network.';
            } else if (jqXHR.status == 404) {
              msg = 'Requested page not found. [404]';
            } else if (jqXHR.status == 500) {
              msg = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
              msg = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
              msg = 'Time out error.';
            } else if (exception === 'abort') {
              msg = 'Ajax request aborted.';
            } else {
              msg = 'Uncaught Error.\n' + jqXHR.responseText;
              let csrf = jqXHR.getResponseHeader('X-CSRF-TOKEN');
              if (csrf == null) {
                msg = "Session has expired. please refresh the page";
              }
            }
            masterAlert('2', '', msg);
          },
        });
      }
    }
  </script>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    {{-- Navbar --}}
    @include('layouts.navbar')

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Main Content --}}
    <div class="content-wrapper pt-3">
      <div class="container-fluid">
        @yield('content')
      </div>
    </div>

    {{-- Footer --}}
    @include('layouts.footer')
  </div>
</body>

</html>