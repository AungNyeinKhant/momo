<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('Subscriber_List') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('email') }}</th>
            <th>{{ translate('subscribed_at') }}</th>
        </thead>
        <tbody>
        @foreach($data['customers'] as $key => $customer)
            <tr>
        <td>{{ $key+1}}</td>
        <td>{{ $customer['email'] }}</td>
        <td>{{ \App\CentralLogics\Helpers::time_date_format($customer->created_at) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
