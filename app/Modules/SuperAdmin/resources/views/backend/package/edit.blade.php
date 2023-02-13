<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Edit Package - {{ $package->title ?? 'N/A'}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="col-xl-12 col-md-12 col-sm-12">
                <form action="{{ route('backend.package.update',':id') }}" method="post" id="update-form" enctype="multipart/form-data">
                    <div class="row">
                        <input type="hidden" class="form-control" id="updateid" value="{{ $package->slug }}">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" placeholder="Enter Title" value = "{{ $package->title }}" name="title">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="text" class="form-control" id="price" placeholder="Enter Price..." value = "{{ $package->price }}" name="price">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option disabled selected>Select Status</option>
                                    <option value="Active" {{ $package->status == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ $package->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class=" col-md-12">
                            <div class="form-group">
                                <label for="remarks">Details</label>
                                <textarea class="form-control summernote" id="remarks" name="remarks">{{ $package->remarks}}</textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary float-right mt-2">Submit</a>
                    <button class="btn btn-danger float-right mt-2 mr-2" data-dismiss="modal">Discard</button>
                </form>
            </div>
        </div>
    </div>
</div>
