@extends('layouts.admin.app')

@section('title',translate('Restaurant_bulk_import'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title mb-2 text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{asset('/public/assets/admin/img/export.png')}}" alt="">
                </div>
                    {{translate('messages.Restaurant_bulk_import')}}
            </h1>
        </div>
        <!-- Content Row -->
        <div class="card">
            <div class="card-body p-2">
                <div class="export-steps style-2">
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{translate('messages.step_1')}}</h5>
                            <p>
                                {{translate('messages.download_excel_file')}}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{translate('messages.step_2')}}</h5>
                            <p>
                                {{translate('messages.match_spread_sheet_data_according_to_instruction')}}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{translate('messages.step_3')}}</h5>
                            <p>
                                {{translate('messages.validate_data_and_comple_import')}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="jumbotron pt-1 pb-4 mb-0 bg-white">
                    <h2 class="mb-3 text-primary">{{ translate('Instructions') }}</h2>
                    <p>{{ translate('1._Download_the_format_file_and_fill_it_with_proper_data.') }}</p>

                    <p><p>{{ translate('2._You_can_download_the_example_file_to_understand_how_the_data_must_be_filled.') }}</p>

                    <p>{{ translate('3._Once_you_have_downloaded_and_filled_the_format_file,_upload_it_in_the_form_below_and_submit.Make_sure_the_phone_numbers_and_email_addresses_are_unique.')}}</p>

                    <p>{{ translate('4._After_uploading_restaurants_you_need_to_edit_them_and_set_restaurants’s_logo_and_cover.') }}</p>

                    <p>{{ translate('5._You_can_get_category_and_zone_id_from_their_list,_please_input_the_right_ids.') }}</p>

                    <p>{{ translate('6._You_can_upload_your_restaurant_images_in_restaurant_folder_from_gallery,_and_copy_image’s_path.') }}</p>

                    <p>{{ translate('7._Default_password_for_restaurant_is_12345678.') }}</p>
                    <p style="color: red" >{{ translate('8._Latitude_must_be_a_number_between_-90_to_90_and_Longitude_must_a_number_between_-180_to_180._Otherwise_it_will_create_server_error')}}</p>
                </div>
                <div class="text-center pb-4">
                    <h3 class="mb-3 export--template-title">{{ translate('Download_Spreadsheet_Template') }}</h3>
                    <div class="btn--container justify-content-center export--template-btns">
                        <a href="{{asset('public/assets/restaurants_bulk_format.xlsx')}}" download=""
                            class="btn btn-dark">{{ translate('Template_with_Existing_Data') }}</a>
                        <a href="{{asset('public/assets/restaurants_bulk_format_nodata.xlsx')}}" download=""
                            class="btn btn-dark">{{ translate('Template_without_Data') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <form class="product-form" action="{{route('admin.restaurant.bulk-import')}}" id="import_form" method="POST"
                enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="button" id="btn_value">
            <div class="card mt-2 rest-part">
                <div class="card-body">
                    <h4 class="mb-3">{{translate('messages.import_restaurants')}}</h4>
                    <div class="custom-file custom--file">
                        <input type="file" name="products_file" class="form-control" id="bulk__import">
                        <label class="custom-file-label" for="bulk__import">{{translate('messages.choose_file')}}</label>
                    </div>
                </div>
                <div class="card-footer border-0">
                    <div class="btn--container justify-content-end">
                        <button id="reset_btn" type="reset" class="btn btn--reset">{{translate('messages.Clear')}}</button>
                        <button type="submit"   name="button" value="update" class="btn btn--warning">{{translate('messages.update')}}</button>
                        <button type="submit"   name="button"  value="import"class="btn btn--primary">{{translate('messages.Import')}}</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

@push('script')
    <script>
        $('#reset_btn').click(function(){
            $('#bulk__import').val(null);
        })
    </script>
        <script>

    $(document).on("click", ":submit", function(e){
        e.preventDefault();
            var data = $(this).val();
            myFunction(data)
    });


    function myFunction(data) {
        Swal.fire({
        title: '{{ translate('Are_you_sure?') }}' ,
        text: "{{ translate('You_want_to_') }}" +data,
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: 'default',
        confirmButtonColor: '#FC6A57',
        cancelButtonText: '{{ translate('no') }}',
        confirmButtonText: '{{ translate('yes') }}',
        reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $('#btn_value').val(data);
                $("#import_form").submit();
            } else {
                toastr.success("{{ translate('Cancelled') }}");
            }
        })
    }
        </script>
@endpush
