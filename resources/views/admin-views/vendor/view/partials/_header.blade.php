
            <!-- Nav -->
            <ul class="nav nav-tabs page-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{request('tab')==null?'active':''}}"  href="{{route('admin.restaurant.view', $restaurant->id)}}">{{translate('messages.overview')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='order'?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'order'])}}"  aria-disabled="true">{{translate('messages.orders')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='product'?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'product'])}}"  aria-disabled="true">{{translate('messages.foods')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='reviews'?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'reviews'])}}"  aria-disabled="true">{{translate('messages.reviews')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='discount'?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'discount'])}}"  aria-disabled="true">{{translate('discounts')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='transaction'?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'transaction'])}}"  aria-disabled="true">{{translate('messages.transactions')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='settings'?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'settings'])}}"  aria-disabled="true">{{translate('messages.settings')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='conversations'?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'conversations'])}}"  aria-disabled="true">{{translate('messages.conversations')}}</a>
                </li>
                @if ($restaurant->restaurant_model != 'none' && $restaurant->restaurant_model != 'commission' )
                <li class="nav-item">
                    <a class="nav-link  {{request('tab') =='subscriptions' || request('tab') =='subscriptions-transactions' ?'active':'' }}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'subscriptions'])}}"  aria-disabled="true">{{translate('messages.subscription')}}</a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link  {{request('tab')=='meta-data' ?'active':''}}" href="{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'meta-data'])}}"  aria-disabled="true">{{translate('messages.meta-data')}}</a>
                </li>
            </ul>
            @if (request('tab') =='subscriptions' || request('tab') =='subscriptions-transactions')
            <ul class="nav nav-tabs page-header-tabs mb-0 mt-3">
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='subscriptions'?'active':''}}" href="{{ route('admin.restaurant.view', ['restaurant' => $restaurant->id, 'tab' => 'subscriptions']) }}">{{ translate('messages.subscription_details') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='subscriptions-transactions'?'active':''}}"  href="{{ route('admin.restaurant.view', ['restaurant' => $restaurant->id, 'tab' => 'subscriptions-transactions']) }}">{{ translate('messages.transactions') }}</a>
                </li>
            </ul>
            @endif
            <!-- End Nav -->
