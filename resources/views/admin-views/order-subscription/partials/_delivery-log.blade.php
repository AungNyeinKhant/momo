@php
$logs = $subscription->logs()->with('delivery_man')->latest()->paginate(config('default_pagination'))->withQueryString();
@endphp
<div class="card">
<div class="card-header">
    <h5 class="card-header-title">{{translate('messages.subscription_delivery_logs')}}<span class="badge badge-soft-dark ml-2">{{$logs->total()}}</span></h5>
</div>
<!-- Table -->
<div class="table-responsive datatable-custom">
    <table id="columnSearchDatatable"
           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
           data-hs-datatables-options='{
             "order": [],
             "orderCellsTop": true,
             "paging":false
           }'>
        <thead class="thead-light">
        <tr>
            <th>{{translate('messages.sl')}}</th>
            <th style="width: 33%" class="text-center">{{translate('messages.time')}}</th>
            <th style="width: 33%">{{translate('messages.status')}}</th>
            <th style="width: 34%">{{translate('messages.DeliveryMan')}}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($logs as $key=>$log)
            <tr>

                <td>{{$key+$logs->firstItem()}}</td>
                @if (isset($log->{$log->order_status}))

                <td class="table-column-pl-0 text-center">
                        {{  \App\CentralLogics\Helpers::time_date_format($log->{$log->order_status}) }}
                </td>
                @else
                <td class="table-column-pl-0 text-center">
                    {{  \App\CentralLogics\Helpers::time_date_format($log->updated_at) }}
                </td>
                @endif
                <td class="text-capitalize">
                    {{ translate($log->order_status) }}
                </td>

                <td>
                    @if ($log->delivery_man)
                        <a href="{{ route('admin.delivery-man.preview', [$log->delivery_man->id]) }}">
                            {{ $log->delivery_man->f_name.' '.$log->delivery_man->l_name }}
                        </a>
                    @else
                        {{  translate('messages.Delivery_Man_Not_Found') }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <!-- Footer -->
    <div class="card-footer">
        <!-- Pagination -->
    {!! $logs->links() !!}
    <!-- End Pagination -->
    </div>
    <!-- End Footer -->
</div>
</div>
