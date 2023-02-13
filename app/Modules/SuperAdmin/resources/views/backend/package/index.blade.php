@extends('layouts.admin.master')

@section('title','| Package')

@section('breadcrumb', 'Package')


@section('content')
  <!--  BEGIN CONTENT AREA  -->

        <div class="row layout-top-spacing">
            {{-- Table --}}
            <div class="col-xl-7 col-lg-6 col-sm-7 ">
                <div class="widget-content widget-content-area br-6">
                    <div class="col-12">
                        <h5 style="display: inline;">Package</h5>
                        {{-- <a href="{{ route("backend.global.option.noticeType.trashedIndex") }}" class="btn btn-danger float-right"><i class="fa fa-trash"></i> Trash</a> --}}
                    </div>
                    <hr>
                    <div class="table-responsive mb-4 mt-4">
                        <table id="global-table" class="table" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="1%">S.No.</th>
                                    <th width="10%">Title</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Price</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- Create Form --}}
             @include('SuperAdmin::backend.package.create')
        </div>
   

    <!-- Modal -->
    <div class="modal animated fadeInUp" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">

    </div>

</div>
<!--  END CONTENT AREA  -->
@endsection


@push('scripts')

<script>
    $('#global-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('backend.package.getPackageData') }}",
        columns: [{
                    "data": 'DT_RowIndex',
                    width:'1%',
                    orderable: false,
                    searchable: false
            },
            {
                data: 'title',
                width:'20%',
                render: function(data, type, row) {
                    return row.title;
                }
            },
            {
                data: 'status',
                width:'10%',
                render: function(data, type, row) {
                    return row.status;
                }
            },
            {
                data: 'price',
                width:'15%',
                render: function(data, type, row) {
                    return row.price;
                }
            },
            {
                data: 'action',
                name: 'action',
                width:'15%',
                orderable: true,
                searchable: true
            },
        ]
    });
</script>

<script>
    $('#remarks').summernote({
        tabsize: 5,
        height: 200,
        toolbar: [
        // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],    
            ['para', ['ul', 'ol', 'paragraph']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
        ]
    });
</script>

@endpush
