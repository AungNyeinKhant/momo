@extends('layouts.landing.app')
@section('title', translate('messages.restaurant_registration'))
@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/landing') }}/css/style.css" />
@endpush
@section('content')
<!-- Page Header Gap -->
<div class="h-148px"></div>
<!-- Page Header Gap -->

<section class="m-0 landing-inline-1 section-gap">
    <div class="container">

        <div class="step__header">
            <h4 class="title">{{ translate('messages.Subscription_Payment') }}</h4>
            <div class="step__wrapper">
                <div class="step__item active">
                    <span class="shapes"></span>
                    {{ translate('messages.general_information') }}
                </div>
                <div class="step__item active">
                    <span class="shapes"></span>
                    {{ translate('messages.business_plan') }}
                </div>
                <div class="step__item {{ request()?->flag == 'success' ||  request()?->type == "new_join?flag=success" ? 'active' : ' ' }} ">
                    <span class="shapes"></span>
                    {{ translate('messages.complete') }}
                </div>
            </div>
        </div>
        <!-- Page Header -->
        {{-- {{ dd(request()?->all()) }} --}}
        <div class="step__header">
            @if (request()?->flag == 'success' ||  request()?->type == "new_join?flag=success")
            <h4 class="title">{{ translate('messages.Payment_Successfull_!') }}</h4>
            @else
            <h4 class="title">{{ translate('messages.Payment_Unsccessfull_!!!') }}</h4>
            @endif
        </div>
        <!-- End Page Header -->
        <div class="card __card">
            <div class="card-body">
                <div class="succeed--status">
                    @php($login_data = App\Models\DataSetting::where('key', 'restaurant_login_url')->pluck('value')->first())

                    @if (request()?->flag == 'success' || request()?->type == "new_join?flag=success")
                    <img class="img" onerror="this.src='{{ asset('public/assets/admin/img/100x100/food-default-image.png') }}'" src="{{ asset('public/assets/admin/img/gif/success.gif') }}">
                        <h4 class="title">{{ translate('Congratulations!') }}</h4>
                        <h5 class="subtitle">
                            {{ translate('messages.Your_Payment_is_Successfull.') }}
                        </h5>
                        <div>
                            @if (request()?->type == 'new_join?flag=success')
                                <h5>
                                    {{ translate('messages.Admin_will_confirm_your_registration_request_or_provide_review.') }}
                                    {{-- <strong> {{ translate('messages.48_hour') }}</strong> --}}
                                </h5>
                            @else
                                <h6>
                                    <a class="text--primary" href="{{ route('login',$login_data) }}">  {{ translate('messages.Login_to_your_restaurant') }} </a>
                                </h6>
                            @endif
                        </div>
                    @else
                    <img class="img" onerror="this.src='{{ asset('public/assets/admin/img/100x100/food-default-image.png') }}'" src="{{ asset('public/assets/admin/img/gif/error.gif') }}">
                        <div>
                            <h4 class="title">{{ translate('Payment_error_!') }}</h4>
                            <h5 class="subtitle">{{ translate('Due_to_payment_transaction_error_your_registration_could_not_complete.') }}</h5>

                        {{-- @if (!isset(request()?->type)) --}}
                        <h6 class="text-primary">
                            <a  class="text--primary" href="{{ route('login',$login_data) }}"> {{ translate('messages.Please_Try_Again') }} </a>
                        </h6>
                        {{-- @endif --}}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
