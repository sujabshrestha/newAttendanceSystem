<div class="col-md-12">
    {{-- <div class="card">
        <div class="card-header">
            <h6>Chairperson Message</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">

                    <label for="chairperson_name">Chairperson Name<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="chairperson_name" name="chairperson_name"
                        value="{{ returnSiteSetting('chairperson_name') ?? old('chairperson_name') }}" placeholder="Enter Chairperson Name"
                        required>
                    @error('chairperson_name')
                        <div class="text-danger">
                            {{ $errors->first('chairperson_name') }}
                        </div>
                    @enderror

                </div>
                <div class="form-group col-md-8"  required style="margin-bottom:0px;">

                    <label for="chairperson_message">Chairperson's Message<span class="text-danger">*</span></label>
    
                    <textarea class="summernote form-control" id="chairperson_message" name="chairperson_message" required style="height: 260px; bottom-margin:0px;" >
                        {{ returnSiteSetting('chairperson_message') ?? old('chairperson_message') }}
                    </textarea>
                    @error('chairperson_message')
                        <div class="text-danger">
                            {{ $errors->first('chairperson_message') }}
                        </div>
                    @enderror

                </div>
    
                <div class="form-group col-md-4" style="  position: absolute; margin: 90px auto;">
                    <label for="chairperson_sign">Chairperson's Signature<span class="text-danger">*</span></label>
                    <input type="file" name="chairperson_sign" class="form-control image-change">
                    <img src="{{ getOrginalUrl(returnSiteSetting('chairperson_sign')) ?? '' }}" class="mt-2" style="height: 100px" alt="">
                    @if ($errors->has('chairperson_sign'))
                        <span class="text-danger">{{ $errors->first('chairperson_sign') }} </span>
                    @endif

                </div>
               
            </div>
        </div>
    </div> --}}
   <div class="card mb-4">
       <div class="card-header">
            <h6>Site details</h6>
       </div>
       <div class="card-body">
        <div class="row">
            <div class="form-group col-md-6">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title"
                    value="{{ returnSiteSetting('title') ?? old('title') }}" name="title" placeholder="Enter Title"
                    required>
                @error('title')
                    <div class="text-danger">
                        {{ $errors->first('title') }}
                    </div>
                @enderror
            </div>
            
            <div class="form-group col-md-6">

                <label for="primary_phone">Primary Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="primary_phone" name="primary_phone"
                    value="{{ returnSiteSetting('primary_phone') ?? old('primary_phone') }}"
                    placeholder="Enter primary phone number" required>
                @error('primary_phone')
                    <div class="text-danger">
                        {{ $errors->first('primary_phone') }}
                    </div>
                @enderror

            </div>
            <div class="form-group col-md-4">

                <label for="secondary_phone">Secondary Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="secondary_phone" name="secondary_phone"
                    value="{{ returnSiteSetting('secondary_phone') ?? old('secondary_phone') }}"
                    placeholder="Enter secondary phone number" required>
                @error('secondary_phone')
                    <div class="text-danger">
                        {{ $errors->first('secondary_phone') }}
                    </div>
                @enderror

            </div>
  
            <div class="form-group col-md-4">

                <label for="primary_email">Primary Email <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="primary_email" name="primary_email"
                    value="{{ returnSiteSetting('primary_email') ?? old('primary_email') }}"
                    placeholder="Enter Primary Email" required>
                @error('primary_email')
                    <div class="text-danger">
                        {{ $errors->first('primary_email') }}
                    </div>
                @enderror

            </div>
            <div class="form-group col-md-4">

                <label for="address">Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="address" name="address"
                    value="{{ returnSiteSetting('address') ?? old('address') }}" placeholder="Enter Address" required>
                @error('address')
                    <div class="text-danger">
                        {{ $errors->first('address') }}
                    </div>
                @enderror

            </div>

            <div class="form-group col-md-6">
                <label for="site_description">Site Description <span class="text-danger">*</span></label>
                <textarea class="summernote" id="site_description" name="site_description"
                placeholder="Enter site description" required> {{ returnSiteSetting('site_description') ?? old('site_description') }} </textarea>
                @error('site_description')
                    <div class="text-danger">
                        {{ $errors->first('site_description') }}
                    </div>
                @enderror
            </div>

            <div class="form-group col-md-6">
                <label for="requirement">Membership Requirements <span class="text-danger">*</span></label>
                <textarea class="summernote" id="requirement" name="requirement"
                placeholder="Enter site description" required> {{ returnSiteSetting('requirement') ?? old('requirement') }} </textarea>
                @error('requirement')
                    <div class="text-danger">
                        {{ $errors->first('requirement') }}
                    </div>
                @enderror
            </div>
        </div>

       </div>
   </div>
    <div class="card mb-4">
        <div class="card-header">
            <h6>Website Links</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-6">

                    <label for="whatsapp">WhatsApp Phone No.<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                        value="{{ returnSiteSetting('whatsapp') ?? old('whatsapp') }}" placeholder="Enter Public Entity Name"
                        required>
                    @error('whatsapp')
                        <div class="text-danger">
                            {{ $errors->first('whatsapp') }}
                        </div>
                    @enderror

                </div>
                <div class="form-group col-md-6">

                    <label for="facebook_link">Facebook Link <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="facebook_link" name="facebook_link"
                        value="{{ returnSiteSetting('facebook_link') ?? old('facebook_link') }}"
                        placeholder="Enter facebook link" required>
                    @error('facebook_link')
                        <div class="text-danger">
                            {{ $errors->first('facebook_link') }}
                        </div>
                    @enderror

                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">

                    <label for="twitter_link">Twitter Link <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="twitter_link" name="twitter_link"
                        value="{{ returnSiteSetting('twitter_link') ?? old('twitter_link') }}"
                        placeholder="Enter twitter link" required>
                    @error('twitter_link')
                        <div class="text-danger">
                            {{ $errors->first('twitter_link') }}
                        </div>
                    @enderror

                </div>
                <div class="form-group col-md-6">

                    <label for="instagra_link">Instagram Link <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="instagram_link" name="instagram_link"
                        value="{{ returnSiteSetting('instagram_link') ?? old('instagram_link') }}"
                        placeholder="Enter instagram link" required>
                    @error('instagra_link')
                        <div class="text-danger">
                            {{ $errors->first('instagram_link') }}
                        </div>
                    @enderror

                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6>Site Images</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site-logo">Site Logo</label>
                        <input type="file" name="logo" class="form-control image-change">


                        <img src="{{ getOrginalUrl(returnSiteSetting('logo')) ?? '' }}" class="mt-2" style="height: 100px" alt="">


                        @if ($errors->has('logo'))
                            <span class="text-danger">{{ $errors->first('logo') }} </span>
                        @endif

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site-favicon">Site Favicon</label>
                        <input type="file" name="favicon"  class="form-control image-change">

                        <img src="{{ getOrginalUrl(returnSiteSetting('favicon')) ?? '' }}"  class="mt-2"  style="height: 100px" alt="">

                    </div>

                    @if ($errors->has('favicon'))
                        <span class="text-danger">{{ $errors->first('favicon') }} </span>
                    @endif
                </div>


            </div>
        </div>

    </div>

    {{-- <div class="col-md-4">
        <div class="form-group">
            <div class="custom-file-container" data-upload-id="aboutUsImage">
                <label>About Us Image <a href="javascript:void(0)" class="custom-file-container__image-clear"
                        title="Clear Image">x</a></label>
                <label class="custom-file-container__custom-file">
                    <input type="file" class="custom-file-container__custom-file__custom-file-input " accept="image/*"
                        name="about_us_image" id="aboutUsImage">
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
                    <span class="custom-file-container__custom-file__custom-file-control"></span>
                </label>
                <div class="custom-file-container__image-preview"></div>
            </div>
        </div>
    </div> --}}


</div>
