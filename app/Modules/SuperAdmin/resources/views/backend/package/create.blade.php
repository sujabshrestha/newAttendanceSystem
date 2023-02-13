<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Create Package</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="feather feather-x">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="col-xl-5 col-lg-6 col-sm-5  layout-spacing">
                <div class="statbox widget box box-shadow" style="padding: 0px">
                    <div class="widget-content widget-content-area">

                        <div class="col-md-12">
                            <h5>Create Leave Type</h5>
                        </div>

                        <hr>
                        <form action="{{ route('backend.package.store') }}" method="post" enctype="multipart/form-data"
                            id="submit-form">

                            <div class="">
                                <div class="form-group col-md-12">
                                    <label for="title">Title</label>
                                    <input type="text" class="form-control" id="title"
                                        value="{{ old('title') }}" placeholder="Enter Title..." name="title"
                                        required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="price">Price</label>
                                    <input type="number" step=any min=0 class="form-control" id="price"
                                        value="{{ old('price') }}" placeholder="Enter Price..." name="price"
                                        required>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option disabled selected>Select Status</option>
                                            <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>
                                                Active</option>
                                            <option value="Inactive"
                                                {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control " id="remarks" name="remarks"> {{ old('remarks') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary float-right mb-3">Submit</a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
