<div class="row">
    <div class="col-lg-12 text-center ">
        <h1> {{ translate('Subcription_Package_Transaction_List') }}
        </h1>
    </div>
    <div class="col-lg-12">

        <table>
            <thead>
                <tr>
                    <th>{{ translate('Search_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        @if ($data['package_name'])
                        {{ translate('Package_Name') }}: {{ $data['package_name'] ?? translate('N/A') }}
                        @endif
                        @if ($data['restaurant_name'])
                        {{ translate('Restautant_name') }}: {{ $data['restaurant_name'] ?? translate('N/A') }}
                        @endif
                        <br>
                        {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}

                        @if ($data['filter'])
                            <br>
                            {{ translate('Filter') }} :
                            @if ($data['filter'] == 'month')
                            {{ translate('messages.This_Month') }}
                            @elseif ($data['filter'] == 'year')
                            {{ translate('messages.This_Year') }}
                            @else
                            {{ translate('messages.All') }}
                            @endif
                        @endif
                        @if ($data['from'])
                            <br>
                            {{ translate('from') }} :
                            {{ $data['from'] ? Carbon\Carbon::parse($data['from'])->format('d M Y') : '' }}
                        @endif
                        @if ($data['to'])
                            <br>
                            {{ translate('to') }} :
                            {{ $data['to'] ? Carbon\Carbon::parse($data['to'])->format('d M Y') : '' }}
                        @endif
                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>


                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('messages.Transaction_Id') }}</th>
                    <th>{{ translate('Transaction_Date') }}</th>


                    @if ($data['package_name'])
                    <th>{{ translate('messages.Restaurant_Name') }}</th>
                    @elseif ($data['restaurant_name'])
                    <th>{{ translate('messages.Package_Name') }}</th>
                    @endif
                    <th>{{ translate('messages.Pricing') }}</th>
                    <th>{{ translate('messages.Duration') }}</th>
                    <th>{{ translate('messages.Payment_Status') }}</th>
                    <th>{{ translate('messages.Payment_Method') }}</th>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $transcation)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ Str::limit($transcation->id, 40, '...') }}</td>
                        <td>
                            {{ \App\CentralLogics\Helpers::date_format($transcation->created_at) }}
                        </td>
                        <td>
                            @if ($data['package_name'])
                            {{ Str::limit($transcation->restaurant->name, 20, '...') }}
                            @elseif ($data['restaurant_name'])
                            {{ $transcation->package->package_name }}
                            @endif


                        </td>

                        <td>{{ \App\CentralLogics\Helpers::format_currency($transcation->package->price) }}</td>
                        <td>{{ $transcation->validity }} {{ translate('messages.Days') }}</td>

                        <td>
                            <div>
                                @if ($transcation->payment_status == 'success')
                                    {{ translate('paid') }}
                                @else
                                    {{ translate('Unpaid') }}
                                @endif

                                {{ \App\CentralLogics\Helpers::format_currency($transcation->paid_amount) }}
                            </div>

                            <br>
                            @if ($transcation->payment_status == 'success')
                                <small class="text-success text-capitalize">
                                    {{ translate($transcation->payment_status) }}
                                </small>
                            @elseif($transcation->payment_status == 'on_hold')
                                <small class="text-warning text-capitalize">
                                    {{ translate('messages.Payment_On_Hold') }}
                                </small>
                            @elseif($transcation->payment_status == 'failed')
                                <small class="text-danger text-capitalize">
                                    {{ translate('messages.Payment_Failed') }}
                                </small>
                            @endif
                        </td>

                        <td>
                            @if ($transcation->payment_method == 'wallet')
                                {{ translate('messages.Wallet_payment') }}
                            @elseif($transcation->payment_method == 'manual_payment_admin')
                                {{ translate('messages.Manual_payment') }}
                            @elseif($transcation->payment_method == 'manual_payment_by_restaurant')
                                {{ translate('messages.Manual_payment') }}
                            @elseif($transcation->payment_method == 'free_trial')
                                {{ translate('messages.free_trial') }}
                            @elseif($transcation->payment_method == 'pay_now')
                                {{ translate('messages.Digital_Payment') }}
                            @else
                                {{ translate($transcation->payment_method) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
