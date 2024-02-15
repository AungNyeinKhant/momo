@extends('layouts.admin.app')

@section('title',translate('Categories_Bulk_Import'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">

        <div class="page-header">
            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{asset('/public/assets/admin/img/export.png')}}" alt="">
                </div>
                {{translate('messages.Categories_Bulk_Import')}}
            </h1>
        </div>
        <!-- Content Row -->
        <div class="card">
            <div class="card-body p-2">
                <div class="export-steps style-2">
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{ translate('STEP_1') }}</h5>
                            <p>
                                {{ translate('Download_Excel_File') }}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{ translate('STEP_2') }}</h5>
                            <p>
                                {{ translate('Match_Spread_sheet_data_according_to_instruction') }}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{ translate('STEP_3') }}</h5>
                            <p>
                                {{ translate('Validate_data_and_and_complete_import') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="jumbotron  pt-1 pb-4 mb-0 bg-white">
                    <h2 class="mb-3 text-primary">{{ translate('messages.instructions') }}</h2>
                    <p> {{ translate('1._Download_the_format_file_and_fill_it_with_proper_data.') }}</p>

                    <p>{{ translate('2._You_can_download_the_example_file_to_understand_how_the_data_must_be_filled.') }}</p>

                    <p>{{ translate('3._Once_you_have_downloaded_and_filled_the_format_file,_upload_it_in_the_form_below_and_submit.')}}</p>

                    <p> {{ translate('4._After_uploading_categories_you_need_to_edit_them_and_set_categorys_images.') }}</p>

                    <p> {{ translate('5._For_parent_category_"position"_will_0_and_for_sub_category_it_will_be_1.') }}</p>

                    <p> {{ translate('6._By_default_status_will_be_1,_please_input_the_right_ids.') }}</p>
                    <p> {{ translate('7._For_Priority_set_0_for_Normal,_1_for_Medium_and_2_for_high.') }}</p>
                    <p> {{ translate('8._For_a_category_parent_id_will_be_empty,_for_sub_category_it_will_be_the_category_id.') }}</p>
                </div>
                <div class="text-center pb-4">
                    <h3 class="mb-3 export--template-title">{{ translate('Download_Spreadsheet_Template')}}</h3>
                    <div class="btn--container justify-content-center export--template-btns">
                        <a href="{{asset('public/assets/categories_bulk_format.xlsx')}}" download=""
                            class="btn btn-dark">{{ translate('Template_with_Existing_Data') }}</a>
                        <a href="{{asset('public/assets/categories_bulk_without_data_format.xlsx')}}" download=""
                            class="btn btn-dark">{{ translate('Template_without_Data') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <form class="product-form" id="import_form"  action="{{route('admin.category.bulk-import')}}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="button" id="btn_value">
            <div class="card mt-2 rest-part">
                <div class="card-body">
                    <h4 class="mb-3">{{translate('messages.Import_Categories_File')}}</h4>
                    <div class="custom-file custom--file">
                        <input type="file" name="products_file" class="form-control" id="bulk__import">
                        <label class="custom-file-label" for="bulk__import">{{ translate('Choose_file') }}</label>
                    </div>
                </div>
                <div class="card-footer border-0">
                    <div class="btn--container justify-content-end">
                        <button id="reset_btn" type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" name="button" value="update" class="btn btn--warning submit_btn">{{translate('messages.update')}}</button>
                        <button type="submit" name="button" value="import" class="btn btn--primary submit_btn">{{translate('messages.Import')}}</button>                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

@push('script_2')
<script>
    $('#reset_btn').click(function(){
        $('#bulk__import').val(null);
    })
</script>
    <script>
$(document).on("click", ".submit_btn", function(e){
    e.preventDefault();
        var data = $(this).val();
        myFunction(data)
});
function myFunction(data) {
    Swal.fire({
    title: '{{ translate('Are_you_sure_?') }}' ,
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
        }
    })
}
    </script>
@endpush
