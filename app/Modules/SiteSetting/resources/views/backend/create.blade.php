@extends('layouts.admin.master')

@section('title', 'Site Setting')

@section('breadcrumb', 'Site Setting')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@section('content') 
    <div class="layout-top-spacing">
        <section class="content">
            <div class="card">
                <div class="card-body">
                    <form enctype="multipart/form-data" method="POST" action="{{ route('backend.siteSetting.store') }}">
                        @csrf
                        @include('SiteSetting::backend.commonForm')

                        <button type="submit" class="btn btn-primary float-right mb-3">Submit</a>
                    </form>
                </div>
            </div>
        </section>

        </section>
        <!--  END CONTENT AREA  -->
    @endsection


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
        <script>
            $('.image-change').change(function() {
                currentthis = $(this);
                let reader = new FileReader();

                reader.onload = (e) => {
                    currentthis.siblings('img').attr('src', e.target.result);
                }

                reader.readAsDataURL(this.files[0]);

            });
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('.summernote').summernote({
                    height: 300
                });
            });
        </script>
    @endpush
