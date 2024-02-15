<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('Customer_List') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Customer_Analytics') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Total_Customer')  }}: {{ $data['customers']->count() }}
                    <br>
                    {{ translate('Active_Customer')  }}: {{ $data['customers']->where('status',1)->count() }}
                    <br>
                    {{ translate('Inactive_Customer')  }}: {{ $data['customers']->where('status',0)->count() }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Content')  }}: : {{ $data['search'] ??translate('N/A') }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('First_Name') }}</th>
            <th>{{ translate('Last_Name') }}</th>
            <th>{{ translate('Phone') }}</th>
            <th>{{ translate('Email') }}</th>
            <th>{{ translate('Saved_Address') }}</th>
            <th>{{ translate('Total_Orders') }}</th>
            <th>{{ translate('Total_Wallet_Amount') }} </th>
            <th>{{ translate('Total_Loyalty_Points') }} </th>
            <th>{{ translate('Status') }} </th>
        </thead>
        <tbody>
        @foreach($data['customers'] as $key => $customer)
            <tr>
        <td>{{ $key+1}}</td>
        <td>{{ $customer['f_name'] }}</td>
        <td>{{ $customer['l_name'] }}</td>
        <td>{{ $customer['phone'] }}</td>
        <td>{{ $customer['email'] }}</td>
        <td>
            @forelse($customer->addresses as $address)
            <br>
            {{ $address->address }}
            <br>
            @empty
            {{  translate('N/A') }}
            @endforelse
        </td>
        <td>{{ $customer['order_count'] }}</td>
        <td>{{  \App\CentralLogics\Helpers::format_currency($customer['wallet_balance'])  }}</td>
        <td>{{ $customer['loyalty_point'] }}</td>
        <td>{{ $customer->status ? translate('messages.Active') : translate('messages.Inactive') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
