@extends('layouts.admin.app')

@section('title',translate('Payment Setup'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('/public/assets/admin/img/payment.png')}}" class="w--22" alt="">
                </span>
                <span>
                    {{translate('messages.payment_gateway_setup')}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
            <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                {{-- <div class="blinkings trx_top active">
                    <i class="tio-info-outined"></i>
                    <div class="business-notes">
                        <h6><img src="{{asset('/public/assets/admin/img/notes.png')}}" alt=""> {{translate('Note')}}</h6>
                        <div>
                            {{translate('Without_configuring_this_section_map_functionality_will_not_work_properly._Thus_the_whole_system_will_not_work_as_it_planned')}}
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card border-0">
            <div class="card-header card-header-shadow">
                <h5 class="card-title align-items-center">
                    <img src="{{asset('/public/assets/admin/img/payment-method.png')}}" class="mr-1" alt="">
                    {{translate('Payment_Method')}}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        @php($config=\App\CentralLogics\Helpers::get_business_settings('cash_on_delivery'))
                        <form action="{{route('admin.business-settings.payment-method-update',['cash_on_delivery'])}}"
                            method="post" id="cash_on_delivery_status_form">
                            @csrf
                            <input type="hidden" name="toggle_type" value="cash_on_delivery" >
                            <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{translate('Cash_On_Delivery')}}
                                    </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('If_enabled_Customers_will_be_able_to_select_COD_as_a_payment_method_during_checkout')}}"><img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                </span>
                                <input class="toggle-switch-input" type="checkbox" id="cash_on_delivery_status" onclick="toogleStatusModal(event,'cash_on_delivery_status','digital-payment-on.png','digital-payment-off.png','{{translate('By_Turning_ON_Cash_On_Delivery_Option')}}','{{translate('By_Turning_OFF_Cash_On_Delivery_Option')}}',`<p>{{translate('Customers_will_not_be_able_to_select_COD_as_a_payment_method_during_checkout._Please_review_your_settings_and_enable_COD_if_you_wish_to_offer_this_payment_option_to_customers.')}}</p>`,`<p>{{translate('Customers_will_be_able_to_select_COD_as_a_payment_method_during_checkout.')}}</p>`)" name="status" value="1" {{$config?($config['status']==1?'checked':''):''}}>

                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                    </div>
                    <div class="col-md-6">
                        @php($digital_payment=\App\CentralLogics\Helpers::get_business_settings('digital_payment'))
                        <form action="{{route('admin.business-settings.payment-method-update',['digital_payment'])}}"
                            method="post" id="digital_payment_status_form">
                            @csrf
                            <input type="hidden" name="toggle_type" value="digital_payment" >
                            <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{translate('digital payment')}}
                                    </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('If_enabled_Customers_will_be_able_to_select_digital_payment_as_a_payment_method_during_checkout')}}"><img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                </span>
                                <input class="toggle-switch-input" type="checkbox" id="digital_payment_status" onclick="toogleStatusModal(event,'digital_payment_status','digital-payment-on.png','digital-payment-off.png','{{translate('By Turning ON Digital Payment Option')}}','{{translate('By Turning OFF Digital Payment Option')}}',`<p>{{translate('Customers will not be able to select digital payment as a payment method during checkout. Please review your settings and enable digital payment if you wish to offer this payment option to customers.')}}</p>`,`<p>{{translate('Customers will be able to select digital payment as a payment method during checkout.')}}</p>`)" name="status" value="1" {{$digital_payment?($digital_payment['status']==1?'checked':''):''}}>

                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($published_status == 1)
        <br>
        <div>
            <div class="card">
                <div class="card-body d-flex flex-wrap justify-content-around">
                    <h4 style="color: #8c1515; padding-top: 10px" class="w-50 flex-grow-1">
                        <i class="tio-info-outined"></i>
                    {{ translate('Your_current_payment_settings_are_disabled,_because_you_have_enabled_payment_gateway_addon,_To_visit_your_currently_active_payment_gateway_settings_please_follow_the_link.') }}</h4>
                    <div>
                        <a href="{{!empty($payment_url) ? $payment_url : ''}}" class="btn btn-outline-primary"> <i class="tio-settings"></i> {{translate('Settings')}}</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @php($is_published = $published_status == 1 ? 'disabled' : '')

           <!-- Tab Content -->
           <div class="row digital_payment_methods mt-3 g-3">
            @foreach($data_values as $payment)
                <div class="col-md-6" style="margin-bottom: 30px">
                    <div class="card">
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.payment-method-update'):'javascript:'}}" method="POST"
                              id="{{$payment->key_name}}-form" enctype="multipart/form-data">
                            @csrf
                            <div class="card-header d-flex flex-wrap align-content-around">
                                <h5>
                                    <span class="text-uppercase">{{ translate($payment->key_name)}}</span>
                                </h5>
                                <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                    <span
                                        class="mr-2 switch--custom-label-text text-primary on text-uppercase">{{ translate('messages.on') }}</span>
                                    <span class="mr-2 switch--custom-label-text off text-uppercase">{{ translate('messages.off') }}</span>
                                    <input type="checkbox" name="status" value="1"
                                           class="toggle-switch-input" {{$payment['is_active']==1?'checked':''}}>
                                    <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                </label>
                            </div>

                            @php($additional_data = $payment['additional_data'] != null ? json_decode($payment['additional_data']) : [])
                            <div class="card-body">
                                <div class="payment--gateway-img">
                                    <img style="height: 80px"
                                         src="{{asset('storage/app/public/payment_modules/gateway_image')}}/{{$additional_data != null ? $additional_data->gateway_image : ''}}"
                                         onerror="this.src='{{asset('/public/assets/admin/img/blank3.png')}}'"
                                         alt="public">
                                </div>

                                <input name="gateway" value="{{$payment->key_name}}" class="d-none">

                                @php($mode=$data_values->where('key_name',$payment->key_name)->first()->live_values['mode'])
                                <div class="form-floating" style="margin-bottom: 10px">
                                    <select class="js-select form-control theme-input-style w-100" name="mode">
                                        <option value="live" {{$mode=='live'?'selected':''}}>{{ translate('Live') }}</option>
                                        <option value="test" {{$mode=='test'?'selected':''}}> {{ translate('Test') }}</option>
                                    </select>
                                </div>

                                @php($skip=['gateway','mode','status'])
                                @foreach($data_values->where('key_name',$payment->key_name)->first()->live_values as $key=>$value)
                                    @if(!in_array($key,$skip))
                                        <div class="form-floating" style="margin-bottom: 10px">
                                            <label for="exampleFormControlInput1"
                                                   class="form-label">{{translate($key)}}
                                                *</label>
                                            <input type="text" class="form-control"
                                                   name="{{$key}}"
                                                   placeholder="{{translate($key)}} *"
                                                   value="{{env('APP_ENV')=='demo'?'':$value}}">
                                        </div>
                                    @endif
                                @endforeach

                                <div class="form-floating" style="margin-bottom: 10px">
                                    <label for="exampleFormControlInput1"
                                           class="form-label">{{translate('payment_gateway_title')}}</label>
                                    <input type="text" class="form-control"
                                           name="gateway_title"
                                           placeholder="{{translate('payment_gateway_title')}}"
                                           value="{{$additional_data != null ? $additional_data->gateway_title : ''}}">
                                </div>

                                <div class="form-floating" style="margin-bottom: 10px">
                                    <label for="exampleFormControlInput1"
                                           class="form-label">{{translate('Choose_logo')}}</label>
                                    <input type="file" class="form-control" name="gateway_image" accept=".jpg, .png, .jpeg|image/*">
                                </div>

                                <div class="text-right" style="margin-top: 20px">
                                    <button type="submit" class="btn btn-primary px-5">{{translate('save')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <!-- End Tab Content -->

    </div>


    <div class="modal fade" id="cod">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center mb-20">
                        <img src="{{asset('/public/assets/admin/img/cod.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{translate('By Turning OFF Cash On Delivery Option')}}</h5>
                        <p class="txt">
                            {{translate("Customers will not be able to select COD as a payment method during checkout. Please review your settings and enable COD if you wish to offer this payment option to customers.")}}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="submit" class="btn btn--primary min-w-120" data-dismiss="modal">{{translate('Ok')}}</button>
                        <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">{{translate("Cancel")}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="digital-payment-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center mb-20">
                        <img src="{{asset('/public/assets/admin/img/modal/digital-payment-off.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{translate('By Turning OFF Digital Payment Option')}}</h5>
                        <p class="txt">
                            {{translate("Disabling digital payments option will disable all available digital payment methods. Customers will not be able to make digital payments during checkout. Please review your settings and consider the impact on customer payment options.")}}
                        </p>
                        <!-- <img src="{{asset('/public/assets/admin/img/modal/digital-payment-on.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{translate('By Turning ON Digital Payment Option')}}</h5>
                        <p class="txt">
                            {{translate("Enabling the digital payments option will allow customers to make payments using digital payment methods during checkout. You can now select your preferred payment method!")}}
                        </p> -->
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="submit" class="btn btn--primary min-w-120" data-dismiss="modal">{{translate('Ok')}}</button>
                        <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">{{translate("Cancel")}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script_2')
<script>
    @if(!isset($digital_payment) || $digital_payment['status']==0)
        $('.digital_payment_methods').addClass('blurry');
    @endif
    $(document).ready(function () {
        $('.digital_payment').on('click', function(){
            if($(this).val()=='0')
            {
                $('.digital_payment_methods').addClass('blurry');
            }
            else
            {
                $('.digital_payment_methods').removeClass('blurry');
            }
        })
    });
    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();

        toastr.success("{{translate('messages.text_copied')}}");
    }

    function checkedFunc() {
        $('.switch--custom-label .toggle-switch-input').each( function() {
            if(this.checked) {
                $(this).closest('.switch--custom-label').addClass('checked')
            }else {
                $(this).closest('.switch--custom-label').removeClass('checked')
            }
        })
    }
    checkedFunc()
    $('.switch--custom-label .toggle-switch-input').on('change', checkedFunc)

</script>
@endpush
