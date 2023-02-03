<script src="{{ asset('backendfiles/assets/js/libs/jquery-3.1.1.min.js') }} "></script>
<script src="{{ asset('backendfiles/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('backendfiles/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('backendfiles/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('backendfiles/assets/js/app.js') }}"></script>
<script>
    $(document).ready(function() {
        App.init();
    });
</script>
<script src="{{ asset('backendfiles/assets/js/custom.js') }}"></script>
<!-- END GLOBAL MANDATORY SCRIPTS -->

{{-- DATATABLES --}}
<script src="{{ asset('backendfiles/plugins/table/datatable/datatables.js') }}"></script>

{{-- SUMMER NOTE --}}
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

{{-- File Upload --}}
<script src="{{ asset('backendfiles/plugins/file-upload/file-upload-with-preview.min.js') }}"></script>

<script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>

<script src="{{ asset('backendfiles/assets/js/loader.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>

{!! Toastr::message() !!}
<script>
    function loader() {
        $.blockUI({
            message: '<div class="spinner-border"><span class="sr-only">Loading...</span> </div>',
            fadeIn: 100,

            overlayCSS: {
                backgroundColor: '#1b2024',
                opacity: 0.8,
                zIndex: 1200,
                cursor: 'wait'
            },
            css: {
                border: 0,
                color: '#fff',
                zIndex: 1201,
                padding: 0,
                backgroundColor: 'transparent'
            }
        });
    }
</script>

<script>
    $(document).on('submit', '#submit-form', function(e) {

        e.preventDefault();
        var currentevent = $(this);
        currentevent.attr('disabled');
        var form = new FormData($('#submit-form')[0]);
        var params = $('#submit-form').serializeArray();
        var route = $(this).attr('action');
        console.log(route);
        $.each(params, function(i, val) {
            form.append(val.name, val.value)
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: route,
            contentType: false,
            processData: false,
            data: form,
            beforeSend: function(data) {
                loader();
            },
            success: function(data) {

                toastr.success(data.message);
                $('#global-table').DataTable().ajax.reload();
                $('#summernote-editor').summernote('code', '');
                $('#submit-form').trigger("reset");
                $('#globalModal').modal('hide');


                currentevent.attr('disabled', false);

            },
            error: function(err) {
                if (err.status == 422) {
                    $.each(err.responseJSON.errors, function(i, error) {
                        var el = $(document).find('[name="' + i + '"]');
                        el.after($('<span style="color: red;">' + error[0] + '</span>')
                            .fadeOut(4000));
                    });
                }

                currentevent.attr('disabled', false);
            },
            complete: function() {
                $.unblockUI();
            }
        });

    });
</script>
{{--
//global edit --}}

<script>
    $(document).on('click', '#create', function(e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#globalModal").html(data.data.view);
                $("#globalModal").modal('show');
            },
        });
    });


    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        var url = $(this).attr('data-url');

        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#globalModal").html(data.data.view);
                $("#globalModal").modal('show');
            },
        });
    });

    $(document).on("submit", "#update-form", function(e) {

        e.preventDefault();
        var currentevent = $(this);
        currentevent.attr('disabled');
        var params = $('#update-form').serializeArray();
        var formData = new FormData($('#update-form')[0]);

        var route = $(this).attr('action');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: route,
            contentType: false,
            processData: false,
            cache: false,
            data: formData,
            beforeSend: function(data) {
                loader();
            },
            success: function(data) {
                toastr.success(data.message);
                $('#globalModal').modal('hide');
                $('#global-table').DataTable().ajax.reload();
            },
            error: function(err) {
                if (err.status == 422) {
                    console.log(err);
                    $.each(err.responseJSON.errors, function(i, error) {
                        var el = $(document).find('[name="' + i + '"]');
                        el.after($('<span style="color: red;">' + error[0] + '</span>')
                            .fadeOut(3000));
                    });
                }
            },
            complete: function() {
                $.unblockUI();
            }
        });
    });

    $(document).on('click', '.delete', function() {

        var currentevent = $(this);
        currentevent.attr('disabled');
        var route = $(this).attr('id');
        swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            padding: '2em'
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    type: "get",
                    url: route,
                    contentType: false,
                    processData: false,
                    beforeSend: function(data) {
                        loader();
                    },
                    success: function(data) {

                        toastr.success('Successfully Deleted !!');
                        $('#global-table').DataTable().ajax.reload();
                        currentevent.attr('disabled', false);
                    },
                    error: function(err) {
                        if (err.status == 422) {
                            $.each(err.responseJSON.errors, function(i, error) {
                                var el = $(document).find('[name="' + i + '"]');
                                el.after($('<span style="color: red;">' + error[0] + '</span>')
                                    .fadeOut(9000));
                            });
                        }

                        currentevent.attr('disabled', false);
                    },
                    complete: function(){
                        $.unblockUI();
                    }
                });
            }
        });
    });


</script>



@stack('scripts')
